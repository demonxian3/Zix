import { pinyin } from './pydict'
export default {
    chineseToPinYin: function (chinese) {
        var length = chinese.length
        var result = ''
        var reg = new RegExp('[a-zA-Z0-9]')
        for (var i = 0; i < length; i++) {
            var val = chinese.substr(i, 1)
            var name = this.arraySearch(val, pinyin)
            if (reg.test(val)) {
                result += val
            } else if (name !== false) {
                result += name
            }
        }
        result = result.replace(/ /g, '-')
        while (result.indexOf('--') > 0) {
            result = result.replace('--', '-')
        }
        return result
    },
    chineseToFirstLetter: function(chinese){
        var length = chinese.length
        var result = ''
        var reg = new RegExp('[a-zA-Z0-9]')
        for (var i = 0; i < length; i++) {
            var val = chinese.substr(i, 1)
            var name = this.arraySearch(val, pinyin)
            if (reg.test(val)) {
                result += val
            } else if (name !== false) {
                result += name[0]
            }
        }
        result = result.replace(/ /g, '-')
        while (result.indexOf('--') > 0) {
            result = result.replace('--', '-')
        }
        return result
    },
    arraySearch: function (chinese) {
        for (var name in pinyin) {
            if (pinyin[name].indexOf(chinese) !== -1) {
                return this.ucfirst(name)
            }
        }
        return false
    },
    ucfirst: function (chinese) {
        if (chinese.length > 0) {
            var first = chinese.substr(0, 1).toUpperCase()
            var spare = chinese.substr(1, chinese.length)
            return first + spare
        }
    }
}