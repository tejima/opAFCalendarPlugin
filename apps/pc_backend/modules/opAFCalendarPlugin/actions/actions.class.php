<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * opAFCalendarPlugin actions.
 *
 * @package    OpenPNE
 * @subpackage opAFCalendarPlugin
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 9301 2008-05-27 01:08:46Z dwhittle $
 */
class opAFCalendarPluginActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfWebRequest $request A request object
  */

  public function executeIndex(sfWebRequest $request)
  {
    $this->form = new opAFCalendarPluginConfigForm();
    if ($request->isMethod(sfWebRequest::POST))
    {
      $this->form->bind($request->getParameter('calendar'));
      if ($this->form->isValid())
      {
        $this->form->save();
        $this->redirect('opAFCalendarPlugin/index');
      }
    }
  }
}
