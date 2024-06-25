<?php

namespace App\Helpers;

use Web3\Contract;
use Web3\Eth;

/**
 * Wraps \Web\Eth to provide synchronous interface.
 *
 * @phpstan-method mixed call(mixed ...$arguments)
 * @phpstan-method string gasPrice(mixed ...$arguments)
 * @phpstan-method \phpseclib3\Math\BigInteger\Engines\GMP getTransactionCount(mixed ...$arguments)
 * @phpstan-method string sendRawTransaction(mixed ...$arguments)
 */
class EthWrapper
{
    public function __construct(private Eth|Contract $eth)
    {
    }

    /**
     * @param array<mixed> $arguments
     */
    public function __call(string $method, array $arguments): mixed
    {
        $result = null;
        $error = null;
        $callback = function ($err, $return) use (&$result, &$error) {
            $result = $return;
            $error = $err;
        };

        $arguments[] = $callback;

        /** @phpstan-ignore-next-line We know that $this->eth receives all methods via __call */
        call_user_func_array([$this->eth, $method], $arguments);

        if ($error) throw new \Exception($error);

        return $result;
    }
}
