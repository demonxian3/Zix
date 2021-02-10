Map.prototype.toOptions = function(start, end) {
    let queue = [];
    let i = 0;
    if (!start) start = 0;
    if (!end) end = this.size - 1;

    for (let [name, value] of this.entries()) {
        if (i >= start && i <= end)
            queue.push({ text: value, value: name });
        i++;
    }
    return queue;
}

Map.prototype.toKeys = function(start, end) {
    let queue = [];
    let i = 0;
    if (!start) start = 0;
    if (!end) end = this.size - 1;
    for (let name of this.keys()) {
        if (i >= start && i <= end)
            queue.push(name);
        i++;
    }
    return queue;
}

Map.prototype.toValues = function(start, end) {
    let queue = [];
    let i = 0;
    if (!start) start = 0;
    if (!end) end = this.size - 1;
    for (let name of this.values()) {
        if (i >= start && i <= end)
            queue.push(name);
        i++;
    }
    return queue;
}

Date.prototype.format = function (fmt) {
    var o = {
        "M+": this.getMonth() + 1, //月份
        "d+": this.getDate(), //日
        "h+": this.getHours(), //小时
        "m+": this.getMinutes(), //分
        "s+": this.getSeconds(), //秒
        "q+": Math.floor((this.getMonth() + 3) / 3), //季度
        "S": this.getMilliseconds() //毫秒
    };
    if (/(y+)/.test(fmt)) fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
    for (var k in o)
        if (new RegExp("(" + k + ")").test(fmt)) fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
    return fmt;
}

Date.prototype.getTimestamp = function(){
    return  Date.parse(new Date()) / 1000;
}

Object.copy = function(obj, change){
    return Object.assign(JSON.parse(JSON.stringify(obj)), change);
}