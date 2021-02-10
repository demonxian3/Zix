import $ from 'jquery';


class Kpman {
  constructor(options) {
    this.host = '';
    this.router = null;
    this.authenticated = false;
    this.api = {_parent: this};
    this.toast = {};
    this.lastError = {};

    this.session = {
      token: '',
      phone: '',
    };

    if (options.setting && options.setting.host) {
      this.setHost(options.setting.host);
    }

    if (options.router) {
      this.router = options.router;
    }

    if (options.api) {
      this.injectApi(options.api)
    }
  }

  injectApi(apis){
    apis.forEach(api => {
      let fn;
      let name = api[0];
      let method = api[1];
      let path = api[2];
      let async = api[3];

      if (async) {
        fn = function(data = {}){
          this._parent.beforeRequest();
          return  this._parent.asyncRequest(method, path, data);
        }
      } else {
        fn = function(data = {}){
          this._parent.beforeRequest();
          return this._parent.syncRequest(method, path, data);
        }
      }

      this.api[name] = fn;
    })
  }

  syncRequest(method, path, data){
    let reply = this.syncAjax(method, path, data);
    let res = this.parseReply(reply);
    return res;
  }

  async asyncRequest(method, path, data){
    let res = {};
    try {
      res = await this.asyncEncapulation(method, path, data);
    } catch(e) {
      res = e;
      this.afterResponse(e);
    }
    return res;
  }

  beforeRequest(){
    this.lastError = {ret: 0, msg: 'ok', text:'ok'};
    if (this.session.token == '' && !this.isLoginPage()) {
      this.toLoginPage();
    }
  }

  logout(){
    this.session.token = '';
    this.session.phone = '';
    this.toLoginPage();
  }

  afterResponse(response){
    if (response) {
      switch(response.status) {
        case 0: 
          this.setErrorInfo(99999);
          break;
        case 401:
          if (!this.isLoginPage()){
            this.toLoginPage();
          }
      }

      const isDef = v => v !== undefined;
      let i = response;
      if (isDef(i = i.responseJSON) && isDef(i = i.data) && isDef(i=i.ret)){
        this.setErrorInfo(response.responseJSON.data.ret);
      }
    }
  }

  setErrorInfo(code){
    let msg = '';
    switch(code){
      case 40001:
        msg = '缺失请求参数，请联系开发人员';
        break;
      case 40101:
        msg = '账号或密码错误，请重新登录';
        break;
      case 40100:
        msg = '会话已失效，请重新登录'
        break;
      case 99999:  
        msg = '网络连接异常，请检查网络';
        break;
    }
    this.lastError = { code, msg }
  }

  //WILLCHANGE
  isLoginPage(){
    return this.$route.path.indexOf('login') >= 0;
  }

  //WILLCHANGE
  toLoginPage(){
    return this.router.replace('/login/password');
  }

  get(url){
    return $.ajax({ url, async:true});
  }

  syncAjax(method, pathinfo, data) {
    let option = {
      url: `${this.host}/${pathinfo}`,
      type: method,
      async: false,
      headers: { 'Token': this.session.phone+this.session.token },
      complete: (xhr, data) => {
        this.afterResponse(xhr);
      },
    }

    if (data) option.data = data;
    return $.ajax(option);
  }

  asyncAjax(method, pathinfo, data, callback) {
    let option = {
      url: `${this.host}/${pathinfo}`,
      type: method,
      async: true,
      headers: { 'Token': this.session.phone + this.session.token },
      complete: (xhr, data) => {
        this.reply = xhr;
        callback(xhr);
        //todo
      },
    }

    if (data) {
      option.data = data;
    }
    $.ajax(option);
    return true;
  }

  asyncJsonp(url, key, callback) {
    return $.ajax({
      url,
      type: 'get',
      jsonp: 'callback',
      jsonpCallback: key,
      dataType: 'jsonp',
      success: callback,
    })
  }

  asyncEncapulation(method, path, data) {
    return new Promise((resolve) => {
      this.asyncAjax(method, path, data, resolve);
    });
  }

  getHost(host) {
    return this.host;
  }

  setHost(host) {
    this.host = host;
  }

  parseReply(reply) {
    let text = typeof reply === 'object' ? reply.responseText : reply;
    if (!text) return text;
    let data = JSON.parse(text);
    return data;
  }

  getFragment() {
    return location.hash.substring(1);
  }

  getQueryString() {
    return location.href.search();
  }

  static install(Vue, options) {
    if (Kpman.installed) {
      return;
    }

    if (!options) {
      throw new Error('options is required');
    }

    if (!Kpman.instance) {
      Kpman.instance = new Kpman(options);
      Kpman.instance.vm = Vue;
    }

    const isDef = v => v !== undefined
    Object.defineProperty(Kpman.instance, '$route', {
      get () {
        let i;
        if (isDef(i=Kpman.instance.router) && (i = i.history) && (i = i.current) ){
          return i;
        }
        return undefined;
      }
    });

    Vue.prototype.$kp = Kpman.instance;
    Vue.prototype.$api = Kpman.instance.api;
    Kpman.installed = true;
  }


}

export default Kpman;
