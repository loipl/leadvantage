<?php

class Controller_Admin_Response extends Controller {

    /**
     * @var Model_LogDelivery
     */
    private $logDelivery;

    private $partners  = array();
    private $usernames = array();
    private $counts    = array();


    public function __construct() {
        parent::__construct();

        $this->logDelivery  = SingletonRegistry::getModelLogDelivery();
    }
    //--------------------------------------------------------------------------


    public function indexAction() {
        $this->fetch();
        $this->groupCountsByPartnerDomain();
    }
    //--------------------------------------------------------------------------


    private function fetch() {
        $this->partners = array();
        foreach (SingletonRegistry::getModelPartner()->listAll('id') as $row) {
            $this->partners[$row['id']] = $row;
        }
        $this->out['partners']  = $this->partners;

        $this->usernames = SingletonRegistry::getModelUser()->listUsernamesAssoc();
        $this->out['usernames'] = $this->usernames;

        $this->counts = $this->logDelivery->listPartnerResponseTimesAndTimeouts();
        $this->out['counts']    = $this->counts;
    }
    //--------------------------------------------------------------------------


    private function groupCountsByPartnerDomain() {
        $domains = array();
        foreach ($this->counts as $row) {
            if (!isset($this->partners[$row['partner_id']])) {
                continue;
            }
            $partner = $this->partners[$row['partner_id']];
            $domain = parse_url($partner['delivery_addr'], PHP_URL_HOST);
            if (!isset($domains[$domain])) {
                $domains[$domain] = array(
                    'sum_rc' => $row['sum_rc'],
                    'sum_rt' => $row['sum_rt'],
                    'sum_ti' => $row['sum_ti'],
                    'avg'    => $row['avg'],
                );
            } else {
                $domains[$domain]['sum_rc'] += $row['sum_rc'];
                $domains[$domain]['sum_rt'] += $row['sum_rt'];
                $domains[$domain]['sum_ti'] += $row['sum_ti'];
            }
        }
        foreach ($domains as & $row) {
            if ($row['sum_rc']) {
                $row['avg'] = number_format($row['sum_rt'] / $row['sum_rc'], 4);
            } else {
                $row['avg'] = 0;
            }
        }
        uasort($domains, function($first, $second) {
            if ($first['avg'] == $second['avg']) {
                return 0;
            }
            return ($first['avg'] > $second['avg']) ? -1 : 1;
        });

        $this->out['domains'] = $domains;
    }
    //--------------------------------------------------------------------------


    public function perPartnerAction() {
        $this->fetch();
    }
    //--------------------------------------------------------------------------
}
