<?php 
class MockInputStreamsWrapper {

private static $mockData = "";
private $pos = 0;

public function __construct($data = "") {
    self::$mockData = json_encode($data);
    $this->pos = 0;
}
public function stream_open($path, $mode, $options, &$opened_path) {
    $contextOptions = stream_context_get_options($this->context);
    if (isset($contextOptions['php']['data'])) {
        self::$mockData = json_encode($contextOptions['php']['data']);
    }
    return true;
}


public function stream_read($count) {
    $data = substr(self::$mockData, $this->pos, $count);
    $this->pos += strlen($data);
    return $data;
}

public function stream_eof() {
    return $this->pos >= strlen(self::$mockData);  // Use self::$mockData
}

    public function stream_stat() {
        return [];
    }

    public function stream_write($data) {
        return 0; // Disable writing, always return 0 bytes written
    }

    public function stream_seek($offset, $whence) {
        switch ($whence) {
            case SEEK_SET:
                if ($offset < strlen($this->data) && $offset >= 0) {
                    $this->pos = $offset;
                    return true;
                }
                return false;
            case SEEK_CUR:
                if ($offset >= 0) {
                    $this->pos += $offset;
                    return true;
                }
                return false;
            case SEEK_END:
                if (strlen($this->data) + $offset >= 0) {
                    $this->pos = strlen($this->data) + $offset;
                    return true;
                }
                return false;
        }
        return false;
    }

    public function stream_tell() {
        return $this->pos;
    }
}
