<?php
class opAFCalendarPluginConfigForm extends sfForm
{
  protected $configs = array(

//s($app['all']['twipne_config']['accesskey'],$app['all']['twipne_config']['secretaccesskey']);
    'googleid' => 'zuniv_us_calendar_googleid',
    'googlepass' => 'zuniv_us_calendar_googlepass',
  );
  public function configure()
  {
    $this->setWidgets(array(
      'googleid' => new sfWidgetFormInput(),
      'googlepass' => new sfWidgetFormInput(),
    ));
    $this->setValidators(array(
      'googleid' => new sfValidatorString(array(),array()),
      'googlepass' => new sfValidatorString(array(),array()),
    ));


    $this->widgetSchema->setHelp('googleid', 'GOOGLE ID');
    $this->widgetSchema->setHelp('googlepass', 'GOOGLE PASS');

    foreach($this->configs as $k => $v)
    {
      $config = Doctrine::getTable('SnsConfig')->retrieveByName($v);
      if($config)
      {
        $this->getWidgetSchema()->setDefault($k,$config->getValue());
      }
    }
    $this->getWidgetSchema()->setNameFormat('calendar[%s]');
  }
  public function save()
  {
    foreach($this->getValues() as $k => $v)
    {
      if(!isset($this->configs[$k]))
      {
        continue;
      }
      $config = Doctrine::getTable('SnsConfig')->retrieveByName($this->configs[$k]);
      if(!$config)
      {
        $config = new SnsConfig();
        $config->setName($this->configs[$k]);
      }
      $config->setValue($v);
      $config->save();
    }
  }
  public function validate($validator,$value,$arguments = array())
  {
    return $value;
  }
}
