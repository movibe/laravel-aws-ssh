<?php namespace Atyagi\LaravelAwsSsh;

use Aws\Common\Aws as ProvidedAWS;
use Illuminate\Foundation\Application;

class AWS {

    /**
     * @var \Aws\Common\Aws
     */
    protected $providedAws;

    public function __construct(ProvidedAWS $aws)
    {
        $this->providedAws = $aws;
    }

    /**
     * @param $instanceId
     * @return string|null
     */
    public function getPublicDNSFromInstanceId($instanceId)
    {
        $ec2Client = $this->providedAws->get('ec2');

        $result = $ec2Client->describeInstances(array(
            'InstanceIds' => array($instanceId)
        ));

        //ugh, no dot notation checks
        $reservations = $result->get('Reservations', array());
        if(count($reservations) > 0) {
            $reservation = $reservations[0];
            if(isset($reservation['Instances']) && count($reservation['Instances']) > 0) {
                $instance = $reservation['Instances'][0];
                return $instance['PublicDnsName'];
            }
        }

        return null;
    }

    public function getPublicDNSFromEBEnvironmentName($envName)
    {
        $ec2Client = $this->providedAws->get('ec2');
        $result = $ec2Client->describeInstances(
            array(
                'Filters' => array(
                    array(
                        'Name' => 'tag-key',
                        'Values' => array('elasticbeanstalk:environment-name')
                    ),
                    array(
                        'Name' => 'tag-value',
                        'Values' => array($envName)
                    )
                )
            )
        );

        $dnsArray = array();
        //ugh, no dot notation checks
        $reservations = $result->get('Reservations', array());
        if(count($reservations) > 0) {
            foreach($reservations as $reservation) {
                if(isset($reservation['Instances']) && count($reservation['Instances']) > 0) {
                    $instance = $reservation['Instances'][0];
                    $dnsArray[] = $instance['PublicDnsName'];
                }
            }
        }

        return $dnsArray;
    }



} 