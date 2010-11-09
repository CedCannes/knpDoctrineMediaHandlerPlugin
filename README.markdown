knpDoctrineMediaHandlerPlugin
=============================

The knpDoctrineMediaHandlerPlugin is a symfony plugin that provides a doctrine behavior making it easier to manage images attached to a model.

What it does
------------

The behavior add some util methods to your model objects. It is also able to automatically remove medias from your filesystem when the object is updated or deleted.

Install the plugin
------------------

To begin, copy the plugin into your plugin folder.

If you want to use git:

    # in your project root directory
    $ git clone git://github.com/knplabs/knpDoctrineMediaHandlerPlugin.git plugins/knpDoctrineMediaHandlerPlugin

Or as a submodule:

    $ git submodule add git://github.com/knplabs/knpDoctrineMediaHandlerPlugin.git plugins/knpDoctrineMediaHandlerPlugin

You must then enable the plugin in your project configuration:

    // config/ProjectConfiguration.class.php
    class ProjectConfiguration extends sfProjectConfiguration
    {
      public function setup()
      {

        // ...

        $this->enablePlugin('knpDoctrineMediaHandlerPlugin');
      }
    }

Use the behavior
----------------

The plugin provide a doctrine template and listener. To use it, you simply need to configure it in the *actAs* section of your model definition.*

Take the exemple of an article:

    # config/doctrine/schema.yml
    Article:
      actAs:
        MediaHandler:
          medias:   [illustration]
      columns:
        title:
          type:     string(127)
          notnull:  true
        illustration:
          type:     string(45)
        body:
          type:     clob

After rebuilding your doctrine model classes:

    $ ./symfony doctrine:build --model

New methods are available for your Article objects:

  * *getMediaPath($field [$filename = null])* gets the path of the media corresponding to the specified field. You can pass an optional filenale: it will calculate the path from the media field folder.
  * *getMediaDirectory($field)* gets the path of the media directory corresponding to the specified field.

The $field parameter is the name of the field configured as media (i.e. "illustration" in the article).
