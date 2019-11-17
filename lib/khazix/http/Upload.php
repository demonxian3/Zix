<?php

declare(strict_types=1);

namespace Khazix\Http;

class Upload
{
    public const IMAGE_MIME_TYPES = ['image/gif', 'image/png', 'image/jpeg', 'image/webp'];

    /*@string filename*/
    private $name;

    /*@string|false|null type */
    private $type;

    /*@int file size*/
    private $size;

    /*@string tmpName */
    private $tmpName;

    /*@int error */
    private $error;

    /*@array files name*/
    private $names;

    /*@array files type*/
    private $types;

    /*@array files size*/
    private $sizes;

    /*@array files tmp_name*/
    private $tmpNames;

    /*@array file error*/
    private $errors;

    /*@bool is multiple upload? */
    private $multipled = false;

    /*@array name => idx mapping table */
    private $mappingTable = [];

    public function __construct(string $key)
    {
        if (!isset($_FILES[$key])) {
            $this->error = UPLOAD_ERR_NO_FILE;
            throw new \Exception('No file uploaded');
            return;
        }

        $files = $_FILES[$key];
        if (is_scalar($files['name'])) {
            $this->name    = $files['name'];
            $this->size    = $files['size'];
            $this->tmpName = $files['tmp_name'];
            $this->error   = $files['error'];
        } else {
            $this->multipled = true;
            foreach ($files['name'] as $idx => $name) {
                $this->mappingTable[$name] = $idx;
            }
            $this->names    = $files['name'];
            $this->sizes    = $files['size'];
            $this->tmpNames = $files['tmp_name'];
            $this->errors   = $files['error'];
        }
    }

    public function getIdx(string $name): ?int
    {
        if ($this->multipled && isset($this->mappingTable[$name])) {
            return $this->mappingTable[$name];
        }
        return null;
    }

    public function getName(): ?string
    {
        $this->checkMultipled(false, 'getNames');
        return $this->name;
    }

    public function getNames(): array
    {
        $this->checkMultipled(true, 'getName');
        return $this->names;
    }

    public function getSize(): int
    {
        $this->checkMultipled(false, 'getSizes');
        return $this->size;
    }

    public function getSizes(string $name = null)
    {
        $this->checkMultipled(true, 'getSize');

        if ($name) {
            $idx = $this->getIdx($name);

            if (NULL !== $idx) {
                return $this->sizes[$idx];
            } else {
                return NULL;
            }
        }

        return $this->sizes;
    }

    public function getTmpName(): string
    {
        $this->checkMultipled(false, 'getTmpNames');

        return $this->tmpName;
    }

    public function getTmpNames(string $name = null)
    {
        $this->checkMultipled(true, 'getTmpName');

        if ($name) {
            $idx = $this->getIdx($name);

            if (NULL !== $idx) {
                return $this->tmpNames[$idx];
            } else {
                return NULL;
            }
        }

        return $this->tmpNames;
    }

    public function getError(): int
    {
        $this->checkMultipled(false, 'getErrors');

        return $this->error;
    }

    public function getErrors(string $name = null)
    {
        $this->checkMultipled(true, 'getError');

        if ($name) {
            $idx = $this->getIdx($name);

            if (NULL !== $idx) {
                return $this->errors[$idx];
            } else {
                return NULL;
            }
        }

        return $this->errors;
    }

    public function isOk (string $name = null): bool
    {
        if (!$this->multipled) {
            return $this->error === UPLOAD_ERR_OK;
        } else {
            if ($name) {
                $idx = $this->getIdx($name);
                if (NULL !== $idx) {
                    return $this->errors[$idx] === UPLOAD_ERR_OK;
                } else {
                    return false;
                }
            } else {
                foreach ($this->errors as $err) {
                    if ($err !== UPLOAD_ERR_OK) {
                        return false;
                    }

                    return true;
                }
            }
        }
    }

    public function hasFile (string $name = null): bool
    {
        if (!$this->multipled) {
            return $this->error !== UPLOAD_ERR_NO_FILE;
        } else {
            if ($name) {
                $idx = $this->getIdx($name);
                if (NULL !== $idx) {
                    return $this->errors[$idx] !== UPLOAD_ERR_NO_FILE;
                } else {
                    return false;
                }
            } else {
                foreach ($this->errors as $err) {
                    if ($err === UPLOAD_ERR_NO_FILE) {
                        return false;
                    }

                    return true;
                }
            }
        }
    }


    public function getContentType(): ?string
    {
        $this->checkMultipled(false, 'getContentTypes');
        if ($this->isOk() && $this->type === null) {
            $this->type =  finfo_file(finfo_open(FILEINFO_MIME_TYPE), $this->tmpName);
        }

        return $this->type;
    }

    public function getContentTypes(string $name = null)
    {
        $this->checkMultipled(true, 'getContentType');

        if ($this->isOk() && $this->types === null) {
            foreach ($this->tmpNames as $tmpName) {
                $this->types[] =  finfo_file(finfo_open(FILEINFO_MIME_TYPE), $tmpName);
            }
        }

        if ($name) {
            $idx = $this->getIdx($name);

            if (NULL !== $idx) {
                return $this->types[$idx];
            } else {
                return null;
            }
        }

        return $this->types;
    }


    public function isImage(string $name = null): bool
    {
        if (!$this->multipled) {
            return in_array($this->getContentType(), self::IMAGE_MIME_TYPES, true);
        }

        if ($name) {
            return in_array($this->getContentTypes($name), self::IMAGE_MIME_TYPES, true);
        } else {
            $types = $this->getContentTypes();

            foreach ($types as $type) {
                if (!in_array($type, self::IMAGE_MIME_TYPES, true)) {
                    return false;
                }
            }
            return true;
        }
    }

    public function getImageSize(string $name = null): ?array
    {
        if ($this->multipled ) {
            if (!$name) {
                throw new \Exception('Multiple files must pass file name');
            }

            $tmpName = $this->getTmpNames($name);
            return $this->isOk($name) ? @getimagesize($tmpName): null;
        } 


        // @ - files smaller than 12 bytes causes read error
        return $this->isOk() ? @getimagesize($this->tmpName) : null; 
    }

    public function getContents(string $name = null): ?string
    {
        if ($this->multipled) {
            if (!$name) {
                throw new \Exception('Multiple files must pass file name');
            }

            $tmpName = $this->getTmpNames($name);
            return $this->isOk($name) ? file_get_contents($tmpName) : null;
        }

        return $this->isOk() ? file_get_contents($this->tmpName) : null;

    }

    public function checkMultipled(bool $multipled, string $suggest)
    {
        if ($this->multipled !== $multipled) {
            throw new \Exception('please use ' .$suggest. ' function for ' .($multipled ? 'multiple files' : 'single file'));
        }
    }

    /* Move uploaded files to new location with random name. */
    public function save(string $dest, string $ext = 'png')
    {
        $this->dest = $dest;
        $this->ext = $ext;
        $this->uniq = 0;

        if (is_file($dest)) {
            throw new \Exception('Arugment:$dest must be a directory');
        }

        if (!file_exists($dest)) {
            $res = mkdir($dest, 0755, true);

            if (!$res) {
                throw new \Exception('Directory create failed');
            }
        }

        if ($this->multipled) {
            $paths = array();
            foreach ($this->tmpNames as $tmpName) {
                $paths[] = $this->saveTmpFiles($tmpName);
            }
            return $paths;
        }

        return $this->saveTmpFiles($this->tmpName);

    }

    public function saveTmpFiles(string $tmpName): string
    {
        if (!is_uploaded_file($tmpName)) {
            throw new \Exception('Not a valid uploaded file');
        }

        $randomName = time() . mt_rand(123, 654) . ($this->uniq++) . '.' . $this->ext;
        $path = $this->dest .DS. $randomName;

        $res = move_uploaded_file($tmpName, $path);
        if (!$res) {
            throw new \Exception('fail to move uploaded file');
        }

        @chmod($path, 0666); //@ - possible low permission to chmod
        return $path;
        
    }

}
