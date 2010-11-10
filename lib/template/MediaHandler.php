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

  /**
   * Indicates whether the field corresponds to an handled media
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
   * Indicates whether the specified field has currently a media
   *
   * @return boolean
   */
  public function hasMedia($field)
  {
    $this->isMediaField($field, true);

    return '' != $this->getInvoker()->get($field);
  }

  /**
   * Gets the path of the media directory
   *
   * @param string $field
   */
  public function getMediaDirectoryPath($field)
  {
    $this->isMediaField($field, true);

    return $this->_handledMedias[$field]['directory']['path'];
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
    $directory = $this->getMediaDirectoryPath($field);

    if (null === $filename)
    {
      if (!$this->hasMedia($field))
      {
        return null;
      }

      $filename = $this->getInvoker()->get($field);
    }

    return $directory . DIRECTORY_SEPARATOR . $filename;
  }

  /**
   * Gets the source of the media directory
   *
   * @throw Exception If the media is not stored under the public directory
   *
   * @return string or null if the media field is empty
   */
  public function getMediaDirectorySrc($field)
  {
    $this->isMediaField($field, true);

    if (!$this->_handledMedias[$field]['directory']['source'])
    {
      throw new Exception(sprintf('The %s field has no source directory.', $field));
    }

    return $this->_handledMedias[$field]['directory']['source'];
  }

  /**
   * Gets the source of the media
   *
   * @throw Exception If the media is not stored under the public directory
   *
   * @return string or null if the media field is empty
   */
  public function getMediaSrc($field)
  {
    if (!$this->hasMedia($field))
    {
      return null;
    }

    return $this->getMediaDirectorySrc($field) . '/' . $this->getInvoker()->get($field);
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
   * Merge default params with the specfied params
   *
   * @param array $params
   *
   * @return array
   */
  protected function mergeDefaultParams(array $params)
  {
    return array_merge($this->_options['params'], $params);
  }

  /**
   * Adds a media field
   *
   * @param string $field
   * @param array  $params
   */
  public function addMediaField($field, array $params = array())
  {
    $params = $this->mergeDefaultParams($params);

    $params['directory']   = $this->configureDirectory($params['directory']);
    $params['auto_remove'] = (bool) $params['auto_remove'];

    $this->_handledMedias[$field] = $params;
  }

  /**
   * @see Doctrine_Template::setTableDefinition()
   */
  public function setTableDefinition()
  {
    foreach ( $this->_options['medias'] as $field => $params )
    {
      if (!is_array($params))
      {
        $field = $params;
        $params = array();
      }
      elseif ( !empty( $params['field'] ) )
      {
        $field = $params['field'];
      }

      $this->addMediaField($field, $params);
    }

    $this->addListener(new MediaHandlerListener($this->_handledMedias));
  }

  /**
   * Gets the path of the default directory
   *
   * @return string
   */
  protected function getDefaultDirectoryPath()
  {
    return sfConfig::get('sf_upload_dir') . DIRECTORY_SEPARATOR . $this->getTable()->getTableName();
  }

  /**
   * Guess the source for the specified directory path
   *
   * @return string or false of the directory source is not under the web directory
   */
  protected function guessDirectorySource($path)
  {
    $web  = str_replace('\\', '/', sfConfig::get('sf_web_dir'));
    $path = str_replace('\\', '/', $path);

    return 0 === strpos($path, $web) ? str_replace($web, '', $path) : false;
  }

  /**
   * Indicates whether the specified path is relative
   *
   * @return boolean
   */
  protected function isPathRelative($path)
  {
    return $path[0] != '/' && $path[0] != '\\' && !(strlen($path) > 3 && ctype_alpha($path[0]) && $path[1] == ':' && ($path[2] == '\\' || $path[2] == '/'));
  }

  /**
   * Complete the path. If the specified path is relative, it prepend the web directory path
   *
   * @return string
   */
  protected function completePath($path)
  {
    if ($this->isPathRelative($path))
    {
      $path = sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . $path;
    }

    return $path;
  }

  /**
   * Configures the directory
   *
   * @param mixed $directory
   *
   * @return array
   */
  protected function configureDirectory($directory)
  {
    if (!is_array($directory))
    {
      $directory = array('path' => $directory);
    }

    if (empty($directory['path']))
    {
      $directory['path'] = $this->getDefaultDirectoryPath();
    }
    else
    {
      $directory['path'] = $this->completePath($directory['path']);
    }

    if (empty($directory['source']))
    {
      $directory['source'] = $this->guessDirectorySource($directory['path']);
    }

    return $directory;
  }
}
