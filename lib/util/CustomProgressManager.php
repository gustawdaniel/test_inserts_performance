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

    /**
     * Class constructor
     */
    public function __construct($current, $max, $width = 80, $doneBarElementCharacter = '=', $remainingBarElementCharacter = ' ', $currentPositionCharacter = '>')
    {
        parent::__construct($current, $max, $width, $doneBarElementCharacter, $remainingBarElementCharacter, $currentPositionCharacter);
        $this->registry->setValue('now', time());
    }

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

            return gmdate("H:i:s", intval( $sec * $max / ($current) ));
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

        $this->addReplacementRule('%eta%', 40, function ($buffer, $registry)
        {
            $advancement    = $registry->getValue('advancement');
            if (count($advancement) == 1)
                return 'Calculating...';

            $seconds               = $advancement['now'] - $advancement[0];
            $percent               = ($registry->getValue('current')) / $registry->getValue('max');
            $estimatedTotalSeconds = intval($seconds / ($percent));

            return gmdate("H:i:s", intval( $estimatedTotalSeconds - $seconds ));

        });
    }

    /**
     * @param Registry $registry
     * @return Integer - time elapsed from start of progress bar in sec
     */
    private function elapsedSec(Registry $registry)
    {
        $advancement    = $registry->getValue('advancement');
        return ($advancement['now'] - $advancement[0]);
    }

    /**
     * Updates current progress
     * Saves new metrics in the registry
     *
     * @param integer $current
     */
    public function update($current)
    {
        if (!is_int($current))
            throw new \InvalidArgumentException('Integer as current counter was expected');

        if ($this->registry->getValue('current') > $current)
            throw new \InvalidArgumentException('Could not set lower current counter');

        if($this->registry->getValue('max') < $current)
            throw new \InvalidArgumentException('Could not set the progress value ' . $current .
                ' because the max is ' . $this->registry->getValue('max'));

        $advancement           = $this->registry->getValue('advancement');
        $advancement['now'] = time();
        $this->registry->setValue('current', $current);
        $this->registry->setValue('advancement', $advancement);
        $lineReturn = ($current == $this->registry->getValue('max'));

        $this->display($lineReturn);
    }


}