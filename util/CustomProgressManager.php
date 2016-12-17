<?php

namespace Util;

use ProgressBar\Manager;
use ProgressBar\Registry;

/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 17.12.16
 * Time: 22:41
 */
class CustomProgressManager extends Manager
{
    protected $format = <<<EOF
%state% : %current%/%max% [%bar%] %percent%% (%elapsed%/%total%) ETA: %eta%
EOF;

    protected function registerDefaultReplacementRules()
    {
        parent::registerDefaultReplacementRules();

        /**
         * Total time approximated as time of processing (ETA + ELAPSED = TOTAL)
         */
        $this->addReplacementRule('%total%', 70, function ($buffer, $registry) {

            $sec = $this->elapsedSec($registry);
            $max = $registry->getValue('max');
            $current = $registry->getValue('current');

            return gmdate("H:i:s", intval( $sec * $max / $current ));
        });

        /**
         * Time from start of progress bar to now
         */
        $this->addReplacementRule('%elapsed%', 70, function ($buffer, $registry) {
            return gmdate("H:i:s", $this->elapsedSec($registry));
        });

        /**
         * Our custom message that explain what is now doing
         */
        $this->addReplacementRule('%state%', 70, function ($buffer, $registry) {
            return $registry->getValue('state');
        });
    }

    /**
     * @param Registry $registry
     * @return Integer - time elapsed from start of progress bar in sec
     */
    private function elapsedSec(Registry $registry)
    {
        $advancement    = $registry->getValue('advancement');
        $current        = $registry->getValue('current');
        return ($advancement[$current] - $advancement[0]);
    }
}