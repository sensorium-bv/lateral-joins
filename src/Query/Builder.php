<?php

namespace Sensorium\LateralJoins\Query;

use Illuminate\Database\Query\Builder as Base;

class Builder extends Base
{
    use BuildsLateralJoins;
}