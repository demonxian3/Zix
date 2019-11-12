class Utils {

    static init()
    {
        this.host = "https://djsp.linz.ac.cn";
    }

    static parse(reply){
        let text = typeof reply == 'object' ? reply.responseText : reply;
        let data = JSON.parse(text);
        return data;
    }

    static getFragment()
    {
        return location.hash.substring(1);
    }

    static getQueryString()
    {
        return  location.href.search();
    }

    static nativeAjax(method, pathinfo, data)
    {
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
        return $.ajax(option);
    }


    static syncAjax(method, pathinfo, data)
    {
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

    static asyncAjax(method, pathinfo, data, callback)
    {

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
            success: callback,
        }

        if (data) option.data = data;

        $.ajax(option);
        return true;
    }

    //跳转页面
    static redirectPage(page){
        window.location = `${page}.html`;
        return true;
    }

    static timeToDate(time) {
        return new Date(parseInt(time) * 1000).toLocaleString().replace(/:\d{1,2}$/,' ');
    }

}
