<?php
namespace Khazix;

/*
 * @params ($key)
 * @method getQuery(String $key="")
 * @method getPost(String $key="")
 * @method getPut(String $key="")
 * @method getDelete(String $key="")
 * @method getPatch(String $key="")
 * @method getCookie(String $key="")
 * @method getSession(String $key="")
 * @method getServer(String $key="")
 * @method getFiles(String $key="")
 * @method getEnv(String $key="")
 * @method saveFile(String $tmpname, String $filename)
 *
 */

class Request
{
    private $uploadPath;

    private $customMethods = [
        'PUT', 'PATCH', 'DELETE'
    ];

    public function __construct(string $uploadPath)
    {
        $this->uploadPath = $uploadPath;
    }

    public function __call($method, $arguments)
    {
        if (0 !== strncmp($method, 'get', 3)) {
            return false;
        }

        $method = strtoupper(substr($method, 3));

        $superName = '_' . $method;

        if ($superName === '_QUERY') {
            $superName = '_GET';
        }

        if (in_array($method, $this->customMethods)) {

            if ($_SERVER['REQUEST_METHOD'] === $method) {

                $GLOBALS[$superName] = array();

                $data = file_get_contents('php://input');

                parse_str($data, $GLOBALS[$superName]);

            }
        }

        if (array_key_exists($superName, $GLOBALS)) {
            if (count($arguments) > 0) {
                if (isset($GLOBALS[$superName][$arguments[0]])) {
                    return $GLOBALS[$superName][$arguments[0]];
                }
                return null;
            }


            return $GLOBALS[$superName];
        }

        return null;

    }

    public function getRequest($key)
    {
        if ($this->getCookie($key)) {
            return $this->getCookie($key);
        }
    }

    public function saveFile($tmpname, $filename): string
    {
        try {

            $savepath = $this->uploadPath .DS. $filename;

            if (!file_exists($this->uploadPath)) {
                throw new \Exception('文件存储路径错误');
            }

            if (file_exists($savepath)) {
                throw new \Exception('文件名已存在');
            }

            move_uploaded_file($tmpname, $savepath);

            return $savepath;

        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

}
