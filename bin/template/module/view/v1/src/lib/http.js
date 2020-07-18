import $ from 'jquery'

export default class Http {
    static setHost(host) {
        this.host = host;
    }

    static parse(reply) {
        let text = typeof reply == 'object' ? reply.responseText : reply;
        let data = JSON.parse(text);
        return data;
    }

    static getFragment() {
        return location.hash.substring(1);
    }

    static getQueryString() {
        return location.href.search();
    }

    static nativeAjax(method, pathinfo, data) {
        let option = {
            url: `${this.host}/${pathinfo}`,
            type: method,
            processData: false,
            contentType: false,
            async: false,
            complete: (xhr, data) => {
                if (xhr.status == 302) {
                    let reply = this.parse(xhr.responseText);
                    top.window.location.href = reply.data;
                    window.location = reply.data;
                    return false;
                }
            },
        }

        if (data) option.data = data;
        return this.parse($.ajax(option));
    }


    static syncAjax(method, pathinfo, data) {
        let option = {
            url: `${this.host}/${pathinfo}`,
            type: method,
            async: false,
            complete: (xhr, data) => {
                if (xhr.status == 302) {
                    let reply = this.parse(xhr.responseText);
                    top.window.location.href = reply.data;
                    window.location = reply.data;
                    return false;
                }
            },
        }

        if (data) option.data = data;
        return $.ajax(option);
    }

    static asyncAjax(method, pathinfo, data, callback) {
        let option = {
            url: `${this.host}/${pathinfo}`,
            type: method,
            async: true,
            complete: (xhr, data) => {
                if (xhr.status == 302) {
                    let reply = this.parse(xhr.responseText);
                    top.window.location.href = reply.data;
                    window.location = reply.data;
                    return false;
                }
                
            },
        }

        if (data) {
            option.data = data;
        }
        if (method.toLowerCase() == 'get'){
            option.success = callback;
        } else {
            option.complete = (xhr, data)=>{
                if (xhr.status == 302){
                    let reply = this.parse(xhr.responseText);
                    top.window.location.href = reply.data;
                    window.location = reply.data;
                } 
                callback(xhr,data);
            }
        }

        $.ajax(option);
        return true;
    }

    /** 同步跨域请求  JSONP */
    static syncAjaxp(method, pathinfo, data) {
        let option = {
            url: `${this.host}/${pathinfo}`,
            type: method,
            dataType: 'jsonp',
            xhrFields: { withCredentials: true },
            crossDomain: true,
            async: false,
            complete: (xhr, data) => {
                if (xhr.status == 302) {
                    let reply = this.parse(xhr.responseText);
                    top.window.location.href = reply.data;
                    window.location = reply.data;
                    return false;
                }
            },
        }
        if (data) option.data = data;
        return $.ajax(option);
    }

}