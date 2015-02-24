<?php

/**
 * Proration calculator
 *
 * Supports Year and Month periods
 */
class Proration
{

    const PERIOD_YEAR = "year";
    const PERIOD_MONTH = "month";
    const PERIOD_WEEK = "week";
    const PERIOD_DAY = "day";
    const PERIOD_ONETIME = "onetime";

    /**
     * @var array List of proratable period
     */
    protected $proratable_periods = array(
        self::PERIOD_YEAR,
        self::PERIOD_MONTH
    );

    /**
     * @var string Start date
     */
    protected $start_date;

    /**
     * @var int The day to prorate to
     */
    protected $prorate_day;

    /**
     *
     * @var int The term
     */
    protected $term;

    /**
     * @var string The period
     */
    protected $period;

    /**
     * Initialize proration
     *
     * @param string $start_date  The date to prorate in ISO 8601 format
     * @param int    $prorate_day The day of the month to prorate to
     * @param int    $term        The term
     * @param string $period      The period for the term
     */
    public function __construct($start_date, $prorate_day, $term, $period)
    {
        $this->start_date = $start_date;
        $this->prorate_day = $prorate_day;
        $this->term = $term;
        $this->period = $period;
    }

    /**
     * Fetches the date to prorate to
     *
     * @return mixed A string containing the date to prorate to
     */
    public function prorateDate()
    {
        if ($this->prorate_day <= 0 || !in_array($this->period, $this->proratable_periods)) {
            return null;
        }

        // Fetch time zone offset of given date
        $offset = substr($this->start_date, 19);

        $start_time = strtotime($this->start_date);
        $current_day = date('j', $start_time);
        $days_in_month = date('t', $start_time);

        $result = null;

        if ($current_day != $this->prorate_day) {
            $first = date('Y-m-01\TH:i:s', $start_time);
            $next_first = strtotime($first . ' + 1 month');

            $time = $start_time;
            $day = $this->prorate_day;

            if ($day > $days_in_month) {
                $day = $days_in_month;
            } elseif ($day < $current_day) {
                $time = $next_first;
            }

            $result = date(
                'Y-m-' . str_pad($day, 2, 0, STR_PAD_LEFT) . '\T00:00:00',
                $time
            ) . $offset;
        }

        return $result;
    }

    /**
     * Determine if proration can occur
     *
     * @return boolean True if proration can occur, false otherwise
     */
    public function canProrate()
    {
        return $this->prorateDays() > 0;
    }

    /**
     * Calculates the number of days to prorate
     *
     * @param  string $from_date The from date
     * @param  string $to_date   The to date
     * @return int The number of days to prorate
     */
    public function prorateDays()
    {
        $to_date = $this->prorateDate();

        if (!$to_date) {
            return 0;
        }

        return $this->daysDiff($this->start_date, $to_date);
    }

    /**
     * Calculate the number of days between two dates
     *
     * @param  string $from
     * @param  string $to
     * @return int
     */
    protected function daysDiff($from, $to)
    {
        $second_per_day = 86400;
        return (int) round(abs(strtotime($from) - strtotime($to)) / $second_per_day);
    }

    /**
     * Calculate the prorated price
     *
     * @param  float $price     The price for a full period
     * @param  int   $precision The number of decimal places of percision
     * @return float The prorated price
     */
    public function proratePrice($price, $precision = 4)
    {
        $days_in_term = $this->daysDiff(
            $this->start_date,
            date(
                "c",
                strtotime($this->start_date . " + " . $this->term . " " . $this->period)
            )
        );
        $prorate_days = $this->prorateDays();

        if ($days_in_term > 0) {
            return round($prorate_days * $price / $days_in_term, $precision);
        }

        return 0.0;
    }
}
