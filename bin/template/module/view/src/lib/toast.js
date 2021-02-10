class Toast {
    constructor(){
        this.autoHideDelay = 2300;
        this.title = '系统提示';
        this.toaster =  'b-toaster-top-center';
        this.variant = '';
        this.methods = {
            'warn':     'warning',
            'error':    'danger',
            'success':  'success',
            'info':     'info',
            'echo':    '',
        };

        this.createMethods();
    }

    createMethods(){
        for (let method in this.methods) {
            let options = {
                title: this.title,
                variant: this.methods[method],
                toaster: this.toaster,
                autoHideDelay: this.autoHideDelay,
            };

            this[method] = function(content, title = ''){
                //_this is Vue instance
                if (title) options.title = title;
                this.$bvToast.toast(content, options);
            }
        }
    }
   


    static install(Vue){
        if (this.installed){
            return
        }

        this.installed = true;

        if (!this.instance){
            this.instance = new Toast();
        }

        Vue.mixin({
            methods: {
                warn: this.instance['warn'],
                error: this.instance['error'],
                success: this.instance['success'],
                info: this.instance['info'],
            }
        })
    }
   
};



export default Toast;