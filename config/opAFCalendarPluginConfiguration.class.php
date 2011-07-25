<?php
class opAFCalendarPluginConfiguration extends sfPluginConfiguration{
  public function initialize()
  {
    sfToolkit::addIncludePath(array(
      sfConfig::get('sf_root_dir').'/lib/vendor/',
    ));
  }
}
