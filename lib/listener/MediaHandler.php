<?php

class MediaHandlerListener extends Doctrine_Record_Listener
{
  protected $_options;

  /**
   * Constructor
   *
   * @param array $options An array of options
   */
  public function __construct(array $options)
  {
    $this->_options = $options;
  }

  /**
   * @see Doctrine_Record::postUpdate()
   */
  public function postUpdate(Doctrine_Event $event)
  {
    $modified = $event->getInvoker()->getModified(true);

    foreach ($this->_options as $field => $params)
    {
      if ($params['auto_remove'] && array_key_exists($field, $modified) && !empty($modified[$field]) && $modified[$field] != $event->getInvoker()->get($field))
      {
        @unlink($event->getInvoker()->getMadiaPath($field, $modified[$field]));
      }
    }
  }

  /**
   * @see Doctrine_Record::postDelete()
   */
  public function postDelete(Doctrine_Event $event)
  {
    foreach ($this->_options as $field => $params)
    {
      if ($params['auto_remove'] && null !== $path = $event->getInvoker()->getMediaPath($field))
      {
        @unlink($path);
      }
    }
  }
}
