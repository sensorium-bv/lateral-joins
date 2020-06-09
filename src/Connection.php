<?php

namespace Sensorium\LateralCte;

use Illuminate\Database\PostgresConnection;

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