<?php

class MediaHandler extends Doctrine_Template
{
  protected $_options = array(
    'medias' => array(),
    'params' => array(
      'directory'   => null,
      'auto_remove' => true,
    )
  );

  /**
   * An array of handled media configurations
   *
   * @var array
   */
  protected $_handledMedias = array();

  public function setTableDefinition()
  {
    foreach ( $this->_options['medias'] as $field => $params )
    {
      if ( !empty( $params['field'] ) )
      {
        $field = $params['field'];
      }

      $params = array_merge( $this->_options['params'], (array) $params );

      $this->_handledMedias[$field] = array(
        'directory'   => empty( $params['directory'] ) ? $this->getDefaultDirectory() : $params['directory'],
        'auto_remove' => $params['auto_remove'] ? true : false,
      );
    }

    $this->addListener( new MediaHandlerListener( $this->_handledMedias ) );
  }

  /**
   * Gets the path of the media
   *
   * @param string $field
   * @param string $filename Media filename (null to use the current value)
   *
   * @return string or null if the media is empty
   */
  public function getMediaPath($field, $filename = null)
  {
    $directory = $this->getMediaDirectory( $field );

    if (null === $filename)
    {
      $filename = $this->getInvoker()->get($field);
    }

    if (empty($filename))
    {
      return null;
    }

    return $directory . DIRECTORY_SEPARATOR . $filename;
  }

  /**
   * Gets the path of the media directory
   *
   * @param string $field
   */
  public function getMediaDirectory($field)
  {
    $this->isMediaField($field, true);

    return $this->_handledMedias[$field]['directory'];
  }

  /**
   * Gets an array of media field names
   *
   * @return array
   */
  public function getMediaFieldNames()
  {
    return array_keys($this->_handledMedias);
  }

  /**
   * Indicates whether the field corresponds to an handled media or not
   *
   * @param string $field
   * @return boolean
   */
  public function isMediaField($field, $orThrowException = false)
  {
    $isHandledMedia = in_array($field, $this->getMediaFieldNames());

    if ($orThrowException && !$isHandledMedia)
    {
      throw new Exception(sprintf('The "%s" field is not an handled media.', $field));
    }

    return $isHandledMedia;
  }

  /**
   * Gets the path of the default directory
   *
   * @return string
   */
  protected function getDefaultDirectory()
  {
    return sfConfig::get('sf_upload_dir') . DIRECTORY_SEPARATOR . $this->getTable()->getTableName();
  }
}
