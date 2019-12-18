Map.prototype.toOptions = function(start, end){
    let queue = [];
    let i = 0;
    if (!start) start = 0;
    if (!end) end = this.size-1;

    for (let [name, value] of this.entries()) {
        if (i >= start && i <= end)
            queue.push({name:value, value:name});
        i++;
    }
    return queue;
}

Map.prototype.toKeys = function(start , end) {
    let queue = [];
    let i = 0;
    if (!start) start = 0;
    if (!end) end = this.size-1;
    for (let name of this.keys()){
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
    if (!end) end = this.size-1;
    for (let name of this.values()){
        if (i >= start && i <= end)
            queue.push(name);
        i++;
    }
    return queue;
}

class Config 
{
    static getHost() {
        return 'http://lamp.shixiongjiaxiao.com';
    }
}
