<?php
class AFCalendarTask extends sfBaseTask
{
  protected function configure()
  {
    set_time_limit(120);
    mb_language("Japanese");
    mb_internal_encoding("utf-8");
    $this->namespace        = 'zuniv.us';
    $this->name             = 'AFCalendar';
    $this->aliases          = array('zuniv.us-afcal');
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [feed-reader|INFO] task does things.
Call it with:

  [php symfony socialagent:feed-reader [--env="..."] application|INFO]
EOF;

    $this->addArgument('googleid', sfCommandArgument::REQUIRED, 'GoogleApps ID');
    $this->addArgument('googlepass', sfCommandArgument::REQUIRED, 'GoogleApps PASSWORD');
  }
  protected function execute($arguments = array(), $options = array())
  {
    //$this->logMessage('QueueTask', 'info');
    //sfOpenPNEApplicationConfiguration::registerZend();

    $databaseManager = new sfDatabaseManager($this->configuration);

    echo "google calendar mode\n";
    $result_arr = self::processCalendar($arguments);
    print sizeof($result_arr);

  }
  public static function processCalendar($arguments){
    self::gc();

    $idlist = unserialize(Doctrine::getTable('SnsConfig')->get("zuniv_us_idlist"));

    //echo "processCalendar()";
    //require_once('Zend/Gdata.php');
    $service = Zend_Gdata_Calendar::AUTH_SERVICE_NAME;
    $client = Zend_Gdata_ClientLogin::getHttpClient($arguments['googleid'],$arguments['googlepass'],$service);
    $service = new Zend_Gdata_Calendar($client);

    $result_arr = array();
    try{
      $query = $service->newEventQuery();
      $query->setUser('default');
      $query->setVisibility('private');
      $query->setProjection('full');
      $query->setOrderby('starttime');
      //$query->setFutureevents('true');
      $q_starttime = strtotime('now');
      //$q_endtime = strtotime('now + 5 minutes');
      $q_endtime = strtotime('now + 60 minutes');
      $query->setStartMin(date('c',$q_starttime));
      $query->setStartMax(date('c',$q_endtime));
      $eventFeed = $service->getCalendarEventFeed($query);
      //echo "after getCalendarEventFeed()";
      //echo sizeof($eventFeed);
      foreach($eventFeed as $event){
        //echo "start time:"  ;
        echo "active events";
        $t_starttime = strtotime($event->when[0]->startTime);
        if($t_starttime >= $q_starttime && $t_starttime < $q_endtime){
          $isdupulicate = $idlist[$event->id->text] ? TRUE : FALSE;
          if($isdupulicate){
            echo "DUPULICATED. PASS.\n";
          }else{
            echo "EVENT_TITLE=>" . $event->title . "\n";
            echo "EVENT_ID=>" . $event->id . "\n";
            print_r($event->id->text);
            print_r($event->content->text);
            $result_arr[] = array("EVENT_ID" => $event->id,"BODY" => $event->title .  '');



            $memberConfig = Doctrine::getTable('MemberConfig')->retrieveByNameAndValue('pop3', $arguments['googleid']);
           


            $act = new ActivityData();
            $act->setMemberId($memberConfig->member_id);
            $act->setBody("そろそろ時間だ：" . $event->title);
            $act->setSource("GoogleCalendar");
            $act->setSourceUri("https://www.google.com/calendar/hosted/tejimaya.com/render");
            $act->setIsMobile(0);
            $act->save();

 

            $idlist[$event->id->text] = $t_starttime;
            Doctrine::getTable('SnsConfig')->set("zuniv_us_idlist", serialize($idlist));
          }
          

          //$event->title = $service->newTitle("【PNE済み】".$event->title);
          //$event->save();
        }else{
          echo "レンジ内のイベントではないのでパス\n";
        }
      }
    }catch(Zend_Gdata_App_Exception $e){
      echo "Exception";
      echo $e->getMessage();
    }
    return $result_arr;
  }
  private static function gc(){
    echo "GC\n";
    //SNSConfig内の過去のデータを削除する。ゴミが無視できないサイズになったら実装する。
    if(FALSE){
    //if(rand(1,100) >= 99){
    echo "GC HIT\n";
      $idlist = unserialize(Doctrine::getTable('SnsConfig')->get("zuniv_us_idlist"));
      arsort($idlist);

      print_r($idlist);
      $counter = 0;
      foreach($idlist as $id => $time){
        if($time < strtotime("now")){
          echo "BREAK\n";
          break;
        }else{
          echo "FUTURE\n";
        }
        $counter++;
      }
      $result = array_slice($idlist,0,$counter);
      $idlist = Doctrine::getTable('SnsConfig')->set("zuniv_us_idlist",serialize($result));
    }
  }
}
