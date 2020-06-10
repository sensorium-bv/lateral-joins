<?php

namespace Sensorium\LateralJoins;

use Illuminate\Database\PostgresConnection;
use Sensorium\LateralJoins\Query\Builder;

class Connection extends PostgresConnection {
    //@Override
    public function query() {
        return new Builder(
            $this,
            $this->getQueryGrammar(),
            $this->getPostProcessor()
        );
    }
}