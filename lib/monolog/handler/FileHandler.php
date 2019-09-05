<?php declare(strict_types=1);

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Monolog\Handler;

use Monolog\Logger;

/**
 * Stores to any stream resource
 *
 * Can be used to store into php://stderr, remote and local files, etc.
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class FileHandler extends AbstractProcessingHandler
{
    /** @var resource|null */
    protected $stream;
    protected $url;
    /** @var string|null */
    private $errorMessage;
    protected $filePermission;
    protected $useLocking;
    private $dirCreated;

    /**
     * @param string        $stream
     * @param string|int    $level          The minimum logging level at which this handler will be triggered
     * @param bool          $bubble         Whether the messages that are handled can bubble up the stack or not
     * @param bool          $useLocking     Try to lock log file before doing any writes
     *
     * @throws \Exception                If a missing directory is not buildable
     * @throws \InvalidArgumentException If stream is not a resource or string
     */
    public function __construct(string $filename, $level = Logger::DEBUG, bool $bubble = true, bool $useLocking = false)
    {
        parent::__construct($level, $bubble);

        if (!is_string($filename)) {
            throw new \InvalidArgumentException('Filename must either be a string.');
        } else if (!file_exists($filename)){
            throw new \InvalidArgumentException('File is not founded.');
        } else {
            $this->filename = $filename;
        }

        $this->useLocking = $useLocking;
    }

    /**
     * {@inheritdoc}
     */
    public function close(): void
    {
        if ($this->stream && is_resource($this->stream)){
            fclose($this->stream);
        }
    }

    /**
     * Return the currently active stream if it is open
     *
     * @return resource|null
     */
    public function getStream()
    {
        return $this->stream;
    }

    /**
     * Return the stream URL if it was configured with a URL and not an active resource
     *
     * @return string|null
     */
    public function getFilename(): ?string
    {
        return $this->filename;
    }

    /**
     * {@inheritdoc}
     */
    protected function write(array $record): void
    {
        if (!is_resource($this->stream)){
            $this->stream = fopen($this->filename, 'a');
        }

        if (!is_resource($this->stream)){
            $this->stream = null;
            throw new \UnexpectedValueException(sprintf('The stream or file "%s" could not be opened: '.$this->errorMessage, $this->url));
        }

        if ($this->useLocking) {
            // ignoring errors here, there's not much we can do about them
            flock($this->stream, LOCK_EX);
        }

        fwrite($this->stream, (string) $record['formatted']);

        if ($this->useLocking) {
            flock($this->stream, LOCK_UN);
        }
    }


}

