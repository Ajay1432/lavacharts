<?php

namespace Khill\Lavacharts;

use \Khill\Lavacharts\Values\Label;
use \Khill\Lavacharts\Configs\Renderable;
use \Khill\Lavacharts\Charts\Chart;
use \Khill\Lavacharts\Dashboards\Dashboard;
use \Khill\Lavacharts\Exceptions\ChartNotFound;
use \Khill\Lavacharts\Exceptions\DashboardNotFound;

/**
 * Volcano Class
 *
 * Storage class that holds all defined charts and dashboards.
 *
 * @category  Class
 * @package   Khill\Lavacharts
 * @since     2.0.0
 * @author    Kevin Hill <kevinkhill@gmail.com>
 * @copyright (c) 2015, KHill Designs
 * @link      http://github.com/kevinkhill/lavacharts GitHub Repository Page
 * @link      http://lavacharts.com                   Official Docs Site
 * @license   http://opensource.org/licenses/MIT MIT
 */
class Volcano
{
    use \Khill\Lavacharts\Traits\NonEmptyStringTrait;

    /**
     * Holds all of the defined Charts.
     *
     * @var array
     */
    private $charts = [];

    /**
     * Holds all of the defined Dashboards.
     *
     * @var array
     */
    private $dashboards = [];

    /**
     * Stores a Chart or Dashboard in the Volcano.
     *
     * @since  3.1.0
     * @param  \Khill\Lavacharts\Charts\Chart|\Khill\Lavacharts\Dashboards\Dashboard $renderable
     * @return \Khill\Lavacharts\Charts\Chart|\Khill\Lavacharts\Dashboards\Dashboard
     */
    public function store(Renderable $renderable)
    {
        if ($renderable instanceof Dashboard) {
            return $this->storeDashboard($renderable);
        }

        if ($renderable instanceof Chart) {
            return $this->storeChart($renderable);
        }
    }

    /**
     * Fetches an existing Chart or Dashboard from the volcano storage.
     *
     * @access public
     * @since  3.1.0
     * @uses   \Khill\Lavacharts\Values\Label
     * @param  string $type Type of Chart or Dashboard.
     * @param  string $label Label of the Chart or Dashboard.
     * @return mixed
     */
    public function get($type, $label)
    {
        $label = new Label($label);

        if ($type == 'Dashboard') {
            return $this->getDashboard($label);
        } else {
            return $this->getChart($type, $label);
        }
    }

    /**
     * Returns all stored charts and dashboards
     *
     * @since  3.1.0
     * @return array All the Renderables
     */
    public function getAll()
    {
        $charts = [];

        foreach ($this->charts as $chartType) {
            foreach ($chartType as $chart) {
                $charts[] = $chart;
            }
        }

        return array_merge($charts, $this->dashboards);
    }

    /**
     * Simple true/false test if a chart exists.
     *
     * @param  string                         $type  Type of chart to check.
     * @param  \Khill\Lavacharts\Values\Label $label Identifying label of a chart to check.
     * @return boolean
     */
    public function checkChart($type, Label $label)
    {
        if ($this->nonEmptyString($type) === false) {
            return false;
        }

        if (array_key_exists($type, $this->charts) === false) {
            return false;
        }

        return array_key_exists((string) $label, $this->charts[$type]);
    }

    /**
     * Simple true/false test if a dashboard exists.
     *
     * @param  \Khill\Lavacharts\Values\Label $label Identifying label of a dashboard to check.
     * @return boolean
     */
    public function checkDashboard(Label $label)
    {
        return array_key_exists((string) $label, $this->dashboards);
    }

    /**
     * Stores a chart in the volcano datastore and gives it back.
     *
     * @access private
     * @param  \Khill\Lavacharts\Charts\Chart $chart Chart to store in the volcano.
     * @return \Khill\Lavacharts\Charts\Chart
     */
    private function storeChart(Chart $chart)
    {
        $this->charts[$chart->getType()][(string) $chart->getLabel()] = $chart;

        return $chart;
    }

    /**
     * Stores a dashboard in the volcano datastore and gives it back.
     *
     * @access private
     * @param  \Khill\Lavacharts\Dashboards\Dashboard $dashboard Dashboard to store in the volcano.
     * @return \Khill\Lavacharts\Dashboards\Dashboard
     */
    private function storeDashboard(Dashboard $dashboard)
    {
        $this->dashboards[(string) $dashboard->getLabel()] = $dashboard;

        return $dashboard;
    }

    /**
     * Retrieves a chart from the volcano datastore.
     *
     * @access private
     * @param  string $type  Type of chart to store.
     * @param  \Khill\Lavacharts\Values\Label $label Identifying label for the chart.
     * @throws \Khill\Lavacharts\Exceptions\ChartNotFound
     * @return \Khill\Lavacharts\Charts\Chart
     */
    private function getChart($type, Label $label)
    {
        if ($this->checkChart($type, $label) === false) {
            throw new ChartNotFound($type, $label);
        }

        return $this->charts[$type][(string) $label];
    }

    /**
     * Retrieves a dashboard from the volcano datastore.
     *
     * @access private
     * @param  \Khill\Lavacharts\Values\Label $label Identifying label for the dashboard.
     * @throws \Khill\Lavacharts\Exceptions\DashboardNotFound
     * @return \Khill\Lavacharts\Dashboards\Dashboard
     */
    private function getDashboard(Label $label)
    {
        if ($this->checkDashboard($label) === false) {
            throw new DashboardNotFound($label);
        }

        return $this->dashboards[(string) $label];
    }
}
