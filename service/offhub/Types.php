<?php
namespace offhub;

/**
 * Autogenerated by Thrift Compiler (0.9.2)
 *
 * DO NOT EDIT UNLESS YOU ARE SURE THAT YOU KNOW WHAT YOU ARE DOING
 *  @generated
 */
use Thrift\Base\TBase;
use Thrift\Type\TType;
use Thrift\Type\TMessageType;
use Thrift\Exception\TException;
use Thrift\Exception\TProtocolException;
use Thrift\Protocol\TProtocol;
use Thrift\Protocol\TBinaryProtocolAccelerated;
use Thrift\Exception\TApplicationException;


final class EventType {
  const FORWARD = 0;
  const COMMENT = 1;
  const ZAN = 2;
  const ZAN_CANCEL = 3;
  static public $__names = array(
    0 => 'FORWARD',
    1 => 'COMMENT',
    2 => 'ZAN',
    3 => 'ZAN_CANCEL',
  );
}

final class SmsType {
  const SECURITY = 0;
  static public $__names = array(
    0 => 'SECURITY',
  );
}

class PostServiceRequest {
  static $_TSPEC;

  /**
   * @var int
   */
  public $tid = null;
  /**
   * @var int
   */
  public $uid = null;
  /**
   * @var string
   */
  public $title = "";
  /**
   * @var string
   */
  public $content = "";
  /**
   * @var string
   */
  public $img = "";
  /**
   * @var string
   */
  public $tags = "";
  /**
   * @var int
   */
  public $type = null;
  /**
   * @var string
   */
  public $f_catalog = null;
  /**
   * @var string
   */
  public $s_catalog = null;
  /**
   * @var int
   */
  public $ctime = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'tid',
          'type' => TType::I32,
          ),
        2 => array(
          'var' => 'uid',
          'type' => TType::I32,
          ),
        3 => array(
          'var' => 'title',
          'type' => TType::STRING,
          ),
        4 => array(
          'var' => 'content',
          'type' => TType::STRING,
          ),
        5 => array(
          'var' => 'img',
          'type' => TType::STRING,
          ),
        6 => array(
          'var' => 'tags',
          'type' => TType::STRING,
          ),
        7 => array(
          'var' => 'type',
          'type' => TType::I32,
          ),
        8 => array(
          'var' => 'f_catalog',
          'type' => TType::STRING,
          ),
        9 => array(
          'var' => 's_catalog',
          'type' => TType::STRING,
          ),
        10 => array(
          'var' => 'ctime',
          'type' => TType::I64,
          ),
        );
    }
    if (is_array($vals)) {
      if (isset($vals['tid'])) {
        $this->tid = $vals['tid'];
      }
      if (isset($vals['uid'])) {
        $this->uid = $vals['uid'];
      }
      if (isset($vals['title'])) {
        $this->title = $vals['title'];
      }
      if (isset($vals['content'])) {
        $this->content = $vals['content'];
      }
      if (isset($vals['img'])) {
        $this->img = $vals['img'];
      }
      if (isset($vals['tags'])) {
        $this->tags = $vals['tags'];
      }
      if (isset($vals['type'])) {
        $this->type = $vals['type'];
      }
      if (isset($vals['f_catalog'])) {
        $this->f_catalog = $vals['f_catalog'];
      }
      if (isset($vals['s_catalog'])) {
        $this->s_catalog = $vals['s_catalog'];
      }
      if (isset($vals['ctime'])) {
        $this->ctime = $vals['ctime'];
      }
    }
  }

  public function getName() {
    return 'PostServiceRequest';
  }

  public function read($input)
  {
    $xfer = 0;
    $fname = null;
    $ftype = 0;
    $fid = 0;
    $xfer += $input->readStructBegin($fname);
    while (true)
    {
      $xfer += $input->readFieldBegin($fname, $ftype, $fid);
      if ($ftype == TType::STOP) {
        break;
      }
      switch ($fid)
      {
        case 1:
          if ($ftype == TType::I32) {
            $xfer += $input->readI32($this->tid);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 2:
          if ($ftype == TType::I32) {
            $xfer += $input->readI32($this->uid);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 3:
          if ($ftype == TType::STRING) {
            $xfer += $input->readString($this->title);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 4:
          if ($ftype == TType::STRING) {
            $xfer += $input->readString($this->content);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 5:
          if ($ftype == TType::STRING) {
            $xfer += $input->readString($this->img);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 6:
          if ($ftype == TType::STRING) {
            $xfer += $input->readString($this->tags);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 7:
          if ($ftype == TType::I32) {
            $xfer += $input->readI32($this->type);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 8:
          if ($ftype == TType::STRING) {
            $xfer += $input->readString($this->f_catalog);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 9:
          if ($ftype == TType::STRING) {
            $xfer += $input->readString($this->s_catalog);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 10:
          if ($ftype == TType::I64) {
            $xfer += $input->readI64($this->ctime);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        default:
          $xfer += $input->skip($ftype);
          break;
      }
      $xfer += $input->readFieldEnd();
    }
    $xfer += $input->readStructEnd();
    return $xfer;
  }

  public function write($output) {
    $xfer = 0;
    $xfer += $output->writeStructBegin('PostServiceRequest');
    if ($this->tid !== null) {
      $xfer += $output->writeFieldBegin('tid', TType::I32, 1);
      $xfer += $output->writeI32($this->tid);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->uid !== null) {
      $xfer += $output->writeFieldBegin('uid', TType::I32, 2);
      $xfer += $output->writeI32($this->uid);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->title !== null) {
      $xfer += $output->writeFieldBegin('title', TType::STRING, 3);
      $xfer += $output->writeString($this->title);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->content !== null) {
      $xfer += $output->writeFieldBegin('content', TType::STRING, 4);
      $xfer += $output->writeString($this->content);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->img !== null) {
      $xfer += $output->writeFieldBegin('img', TType::STRING, 5);
      $xfer += $output->writeString($this->img);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->tags !== null) {
      $xfer += $output->writeFieldBegin('tags', TType::STRING, 6);
      $xfer += $output->writeString($this->tags);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->type !== null) {
      $xfer += $output->writeFieldBegin('type', TType::I32, 7);
      $xfer += $output->writeI32($this->type);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->f_catalog !== null) {
      $xfer += $output->writeFieldBegin('f_catalog', TType::STRING, 8);
      $xfer += $output->writeString($this->f_catalog);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->s_catalog !== null) {
      $xfer += $output->writeFieldBegin('s_catalog', TType::STRING, 9);
      $xfer += $output->writeString($this->s_catalog);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->ctime !== null) {
      $xfer += $output->writeFieldBegin('ctime', TType::I64, 10);
      $xfer += $output->writeI64($this->ctime);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}

class EventServiceRequest {
  static $_TSPEC;

  /**
   * @var int
   */
  public $type = null;
  /**
   * @var int
   */
  public $tid = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'type',
          'type' => TType::I32,
          ),
        2 => array(
          'var' => 'tid',
          'type' => TType::I32,
          ),
        );
    }
    if (is_array($vals)) {
      if (isset($vals['type'])) {
        $this->type = $vals['type'];
      }
      if (isset($vals['tid'])) {
        $this->tid = $vals['tid'];
      }
    }
  }

  public function getName() {
    return 'EventServiceRequest';
  }

  public function read($input)
  {
    $xfer = 0;
    $fname = null;
    $ftype = 0;
    $fid = 0;
    $xfer += $input->readStructBegin($fname);
    while (true)
    {
      $xfer += $input->readFieldBegin($fname, $ftype, $fid);
      if ($ftype == TType::STOP) {
        break;
      }
      switch ($fid)
      {
        case 1:
          if ($ftype == TType::I32) {
            $xfer += $input->readI32($this->type);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 2:
          if ($ftype == TType::I32) {
            $xfer += $input->readI32($this->tid);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        default:
          $xfer += $input->skip($ftype);
          break;
      }
      $xfer += $input->readFieldEnd();
    }
    $xfer += $input->readStructEnd();
    return $xfer;
  }

  public function write($output) {
    $xfer = 0;
    $xfer += $output->writeStructBegin('EventServiceRequest');
    if ($this->type !== null) {
      $xfer += $output->writeFieldBegin('type', TType::I32, 1);
      $xfer += $output->writeI32($this->type);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->tid !== null) {
      $xfer += $output->writeFieldBegin('tid', TType::I32, 2);
      $xfer += $output->writeI32($this->tid);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}

class FollowEvent {
  static $_TSPEC;

  /**
   * @var int
   */
  public $uid = null;
  /**
   * @var int
   */
  public $follower_uid = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'uid',
          'type' => TType::I32,
          ),
        2 => array(
          'var' => 'follower_uid',
          'type' => TType::I32,
          ),
        );
    }
    if (is_array($vals)) {
      if (isset($vals['uid'])) {
        $this->uid = $vals['uid'];
      }
      if (isset($vals['follower_uid'])) {
        $this->follower_uid = $vals['follower_uid'];
      }
    }
  }

  public function getName() {
    return 'FollowEvent';
  }

  public function read($input)
  {
    $xfer = 0;
    $fname = null;
    $ftype = 0;
    $fid = 0;
    $xfer += $input->readStructBegin($fname);
    while (true)
    {
      $xfer += $input->readFieldBegin($fname, $ftype, $fid);
      if ($ftype == TType::STOP) {
        break;
      }
      switch ($fid)
      {
        case 1:
          if ($ftype == TType::I32) {
            $xfer += $input->readI32($this->uid);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 2:
          if ($ftype == TType::I32) {
            $xfer += $input->readI32($this->follower_uid);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        default:
          $xfer += $input->skip($ftype);
          break;
      }
      $xfer += $input->readFieldEnd();
    }
    $xfer += $input->readStructEnd();
    return $xfer;
  }

  public function write($output) {
    $xfer = 0;
    $xfer += $output->writeStructBegin('FollowEvent');
    if ($this->uid !== null) {
      $xfer += $output->writeFieldBegin('uid', TType::I32, 1);
      $xfer += $output->writeI32($this->uid);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->follower_uid !== null) {
      $xfer += $output->writeFieldBegin('follower_uid', TType::I32, 2);
      $xfer += $output->writeI32($this->follower_uid);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}

class SmsRequest {
  static $_TSPEC;

  /**
   * @var string
   */
  public $mobile = null;
  /**
   * @var string
   */
  public $content = null;
  /**
   * @var int
   */
  public $send_time = 0;
  /**
   * @var int
   */
  public $type =   0;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'mobile',
          'type' => TType::STRING,
          ),
        2 => array(
          'var' => 'content',
          'type' => TType::STRING,
          ),
        3 => array(
          'var' => 'send_time',
          'type' => TType::I32,
          ),
        4 => array(
          'var' => 'type',
          'type' => TType::I32,
          ),
        );
    }
    if (is_array($vals)) {
      if (isset($vals['mobile'])) {
        $this->mobile = $vals['mobile'];
      }
      if (isset($vals['content'])) {
        $this->content = $vals['content'];
      }
      if (isset($vals['send_time'])) {
        $this->send_time = $vals['send_time'];
      }
      if (isset($vals['type'])) {
        $this->type = $vals['type'];
      }
    }
  }

  public function getName() {
    return 'SmsRequest';
  }

  public function read($input)
  {
    $xfer = 0;
    $fname = null;
    $ftype = 0;
    $fid = 0;
    $xfer += $input->readStructBegin($fname);
    while (true)
    {
      $xfer += $input->readFieldBegin($fname, $ftype, $fid);
      if ($ftype == TType::STOP) {
        break;
      }
      switch ($fid)
      {
        case 1:
          if ($ftype == TType::STRING) {
            $xfer += $input->readString($this->mobile);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 2:
          if ($ftype == TType::STRING) {
            $xfer += $input->readString($this->content);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 3:
          if ($ftype == TType::I32) {
            $xfer += $input->readI32($this->send_time);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 4:
          if ($ftype == TType::I32) {
            $xfer += $input->readI32($this->type);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        default:
          $xfer += $input->skip($ftype);
          break;
      }
      $xfer += $input->readFieldEnd();
    }
    $xfer += $input->readStructEnd();
    return $xfer;
  }

  public function write($output) {
    $xfer = 0;
    $xfer += $output->writeStructBegin('SmsRequest');
    if ($this->mobile !== null) {
      $xfer += $output->writeFieldBegin('mobile', TType::STRING, 1);
      $xfer += $output->writeString($this->mobile);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->content !== null) {
      $xfer += $output->writeFieldBegin('content', TType::STRING, 2);
      $xfer += $output->writeString($this->content);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->send_time !== null) {
      $xfer += $output->writeFieldBegin('send_time', TType::I32, 3);
      $xfer += $output->writeI32($this->send_time);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->type !== null) {
      $xfer += $output->writeFieldBegin('type', TType::I32, 4);
      $xfer += $output->writeI32($this->type);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}


