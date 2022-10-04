<?php namespace Edrisa\Command\Stream;

use stdClass as Storage;
use Edrisa\Command\Command;

class CallbackReaderTest extends \PHPUnit_Framework_TestCase {

    public function testConstructor()
    {
        $stream = string_to_stream("foobar");
        $buffer_size = 1000;

        $callback_data = new Storage();
        $callback_data->calls = 0;
        $callback = function($pipe, $data) use ($callback_data) {
            $callback_data->calls++;
        };

        $reader = new CallbackReader($stream, 0, $callback, $buffer_size);

        while (!feof($stream)) {
            $reader->read();
        }

        $this->assertGreaterThan(0, $callback_data->calls);
    }

    public function testCallbackReceivesSameData()
    {
        $value = get_test_string();
        $value_size = strlen($value);
        $stream = string_to_stream($value);

        $stream_id = Command::STDOUT;
        $buffer_size = 1000;

        $callback_data = new Storage();
        $callback_data->bytes = 0;
        $callback_data->calls = 0;
        $callback_data->buffer = '';
        $callback_data->stream_id = $stream_id;

        $phpunit = $this;

        $callback = function($pipe, $data) use ($callback_data, $phpunit) {
            $callback_data->calls++;
            $callback_data->bytes += strlen($data);
            $callback_data->buffer .= $data;
            $phpunit->assertSame($callback_data->stream_id, $pipe);
        };

        $reader = new CallbackReader($stream, $stream_id, $callback, $buffer_size);

        $this->assertEmpty($callback_data->buffer);
        $this->assertSame(0, $reader->getBytes());

        $iterations = 0;
        $bytes = 0;
        while (!feof($stream)) {
            $iterations++;
            $bytes += $reader->read();
        }

        // Make sure we read the same number of bytes
        $this->assertSame($value_size, $bytes);
        $this->assertSame($value_size, $reader->getBytes());
        $this->assertSame($value_size, $callback_data->bytes);

        // Make sure the data is copied to the buffer and is identical
        $this->assertSame(md5($value), md5($callback_data->buffer), "I/O itegrity check failed");

        $min_iterations = ceil($value_size / $buffer_size);
        $this->assertGreaterThanOrEqual($min_iterations, $iterations, "Finished reading the stream in $iterations iterations, but with a buffer_size of $buffer_size and $value_size bytes of data, it must have taken at least $min_iterations iterations");
    }

    /**
     * @expectedException Edrisa\Command\TerminateException
     */
    public function testTerminateFromCallback()
    {
        $value = get_test_string();
        $stream = string_to_stream($value);
        $buffer_size = 1000;

        $callback = function($pipe, $data) {
            // Returning false tells Command to throw a TerminateException
            return false;
        };

        $reader = new CallbackReader($stream, 0, $callback, $buffer_size);

        while (!feof($stream)) {
            $reader->read();
        }
    }
}
