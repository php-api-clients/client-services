<?php
declare(strict_types=1);

namespace WyriHaximus\Travis\Resource;

use WyriHaximus\ApiClient\Resource\TransportAwareTrait;

abstract class SSHKey implements SSHKeyInterface
{
    use TransportAwareTrait;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $fingerprint;

    /**
     * @return string
     */
    public function description() : string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function fingerprint() : string
    {
        return $this->fingerprint;
    }
}