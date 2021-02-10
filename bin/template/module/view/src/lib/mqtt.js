import Mqtt from 'mqtt';

export default class MqttClient {
    constructor(url, options) {
        this.url = url;
        this.options = options;
        this.client = {};
        this.options.clientId = 'IPS_APP_' + Date.now();
        this.topicList = [];
        this.listenerQueue = {};

        //重连最大次数，防止无限重连，默认两次
        this.reconnectMaxCount = 2;
        this.reconnectCurCount = 0;
    }

    connect() {
        if (this.isEmptyClient()) {
            this.client = Mqtt.connect(this.url, this.options);
            this.client.on('connect', this.onConnect);
            this.client.on('reconnect', this.onReconnect);
            this.client.on('error', this.onError);
            // this.client.on('message', this.onMessage);
            this.client.on('close', this.onClose);
        }

        return this.client;
    }

    createListener(tag, fn){
        if (this.isEmptyClient()){
            return false;
        }

        this.removeListener(tag);        
        this.listenerQueue[tag] = fn;
        this.client.on('message', fn);
    }

    removeListener(tag){
        if (this.listenerQueue.hasOwnProperty(tag)){
            console.log('移除已存在的监听事件')
            this.client.removeListener('message', this.listenerQueue[tag]);
            delete this.listenerQueue[tag];
        }
    }

    unsubscribeAll(){
        if (this.isEmptyClient()) {
            return false;
        }

        while(this.topicList.length > 0) {
            let topic = this.topicList.pop();
            this.client.unsubscribe(topic);
        }
    }

    hasListen(topic){
        return this.topicList.indexOf(topic) >= 0;
    }

    subscribe(topic, qos, callback) {
        if (this.isEmptyClient()) {
            return false;
        }

        if (this.topicList.indexOf(topic) >= 0) {
            return true;
        }

        this.topicList.push(topic);
        this.client.subscribe(topic, [{qos}], callback);
    }

    publish(topic, jsonStr, options={}){
        if (this.isEmptyClient()) {
            return false;
        }
        this.client.publish(topic, jsonStr, options);
    }

    isEmptyClient() {
        return JSON.stringify(this.client) === '{}';
    }

    isConnected() {
        if (this.client && this.client.connected && this.client.disconnecting == false) {
            return true;
        }
        return false;
    }

    reconnect() {
        if (this.client && this.client.reconnect) {
            this.client.reconnect();
        }
    }

    close() {
        if (this.client.hasOwnProperty('connected') && this.client.connected) {
            this.client.end();
        }
    }
    onConnect() {
        console.log('连接成功');
    }
    onReconnect() {
        if (!this.maxReconnectCount) {
            this.maxReconnectCount = 0;
        } else {
            this.maxReconnectCount++;
        }
        console.log('最大重连数，防止无限重连', this.maxReconnectCount);
        //注意：这里的this 不是class mqttClient的对象，而是 client本身，可以直接用end方法
        console.log('尝试重连，若clientID相同，服务器上次未释放导致无限重连');
    }
    onError(err) {
        if (this.connected == false) {
            
            this.reconnect();
        }
        console.log('发生错误' + err);
    }
    onMessage(msg, topic) {
        console.log(topic + '主题消息:' + msg);
    }
    onClose(){
        //todo long time disconnect, need reconnect
        if (this.connected == false) {
            this.reconnect();
        }
    }

    changeTopicPrefix(topic, newPrefix){
        if (topic && typeof topic === 'string') {
            let parts = topic.split('/');
            if (parts.length == 3){
                parts[0] = newPrefix;
            }
            return parts.join('/');
        }
        return '';
    }


}