import $http from '../lib/http.js'

export default class API {
    static asyncEncapulation(method, path, data){
        return new Promise((resolve)=>{
            $http.asyncAjax(method, path, data, resolve);
        })
    }

    static getSpecList(){
        return this.asyncEncapulation('get', 'cms/specification', {});
    }

    static getTagList(){
        return this.asyncEncapulation('get', 'cms/tag', {});
    }

    static getCategoryList(){
        return this.asyncEncapulation('get', 'cms/category', {});
    }

    static getCategoryTree(){
        return this.asyncEncapulation('get', 'cms/category/getCategoryTree', {});
    }

    static getCategoryRoot(){
        return this.asyncEncapulation('get', 'cms/category/getCategoryRoot', {});
    }

    static getCommodityList(page, filter){
        return this.asyncEncapulation('get', 'cms/commodity', {
            page,
            filter,
        });
    }

    static getPageCount(filter){
        return this.asyncEncapulation('get', 'cms/commodity/getPageCount', {filter});
    }
    
    static getCommodityStatus(){
        return this.asyncEncapulation('get', 'cms/commodity/getCommodityStatusMap', {});
    }
    
    static delCommodity(id){
        return this.asyncEncapulation('delete', 'cms/commodity/'+id, {});
    }

    static batchDelCommodity(ids){
        return this.asyncEncapulation('delete', 'cms/commodity/batchDeleteCommodity', {ids});
    }

    static batchUpdCommodity(ids, data){
        data.ids = ids;
        return this.asyncEncapulation('patch', 'cms/commodity/batchUpdateCommodity', data);
    }

    static addCommodity(data){
        return this.asyncEncapulation('post', 'cms/commodity', data);
    }

    static updCommodity(id, data){
        return this.asyncEncapulation('patch', 'cms/commodity/'+id, data);
    }

    static updCategory(id,data){
        return this.asyncEncapulation('patch', 'cms/category/'+id, data);
    }

    static addTag(data){
        return this.asyncEncapulation('post', 'cms/tag', data);
    }

    static delTag(id){
        return this.asyncEncapulation('delete', 'cms/tag/'+id);
    }

    static addCategory(data){
        return this.asyncEncapulation('post', 'cms/category', data);
    }

    static delCategory(id){
        return this.asyncEncapulation('delete', 'cms/category/'+id);
    }

    static addSpecification(data){
        return this.asyncEncapulation('post', 'cms/specification', data);
    }

    static delSpecification(id){
        return this.asyncEncapulation('delete', 'cms/specification/'+ id);
    }
}