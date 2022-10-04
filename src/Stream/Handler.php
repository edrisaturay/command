<?php namespace Edrisa\Command\Stream;

/**
 * Manages an IO handle/resource and is able to provide stats about the bytes affected.
 * @package Edrisa\Command\Stream
 */
interface Handler {

    public function getBytes();

}
