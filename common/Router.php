<?php
namespace Common;

class Router {
    const config = [
        ['GET',     '/manage/goods',        'searchGood'],
        ['PUT',     '/manage/goods',        'updateGood'],
        ['POST',    '/manage/goods',        'createGood'],
        ['DELETE',  '/manage/goods',        'deleteGood'],

        //['ANY',     '/wxaccount/index',     'main'],
        ['ANY',     '/wxaccount/index',     'test'],

        ['ANY', '/testing/index', 'test'],
    ];
}
