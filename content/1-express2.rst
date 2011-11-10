Title: Building a minimal blogging engine
Tags: meta, php, express2

A short description of *Express 2*, an extremely minimal blogging engine that
took me like two years to build.

As any computer geek or DIY:er can attest, there's something about making
things yourself. Sometimes it's a really bad idea, but in the end, at least you
come out of it learning something.


What is Express 2?
------------------

Express 2 is a small piece of software with one objective:

* Publish timestamped articles with tags, in HTML and as an Atom feed.

That is, a simple blog. Express 2 does this by generating static HTML files
from a collection of entries stored in plain text files.

It is written in PHP, because that is what I know (which is a pretty good
argument, if you ask me).


Core architecture
-----------------

Let's begin with the directory structure. This is the Express 2 root
directory::

	build/
	content/
	static/
	templates/
	express2

``build/``
	The generated content. This is what will be served by the web server.

``content/``
	All entries, as plain text files. More on this later.

``static/``
	Static resources like images, CSS and JavaScript. Will be copied to the
	``build/`` directory.

``templates/``
	Templates for HTML/Atom.

``express2``
	The Express 2 engine. Execute to generate content.

Express 2 stores entries in plain text files. This lets me use any text editor
to write and edit content (currently `MacVim <http://macvim.org/>`_). Entries
consist of metadata and content, represented in an HTTP-style format. In
addition, there is support for writing the entry content in either plain HTML
or using ReST markup that will be automatically converted to HTML (this
conversion is made by what I call a *content parser*).

Let's illustrate this by creating an entry: ``content/1-hello_world.html``::

	Title: Hello world
	Tags: hello, world
	Created: 2011-11-06 10:00
	Updated: 2011-11-06 11:00
	Parser: html
	Template: default
	Url: hello_world.html

	<strong>Hello</strong>, World!

If you are familiar with the `HTTP protocol
<http://www.ietf.org/rfc/rfc2616.txt>`_, you will instantly recognize this.
Metadata is given as key-value pairs, followed by the content of the entry,
separated by exactly one blank line. Neat thing about this is that it allows me
to use an existing parser, in this case the ``http_parse_message()`` function
from the `PECL HTTP extension <http://pecl.php.net/package/pecl_http>`_.

The different headers pretty much explain themselves. The ``template`` and
``parser`` headers define what template to use and what parser to use for the
content. In this case, the template ``templates/default.html.php`` will be
used, and the content will be parsed by the function named ``parse_html()``.

The ``Url`` header define the *relative* URL. That is, when I upload the
generated content to my blog, the URL of this entry will be
http://david.hgbrg.se/blog/hello_world.html.

Some headers are given default values however, so the above could be shortened
to::

	Title: Hello world
	Tags: hello, world

	Hello, World!

The ``Created`` and ``Updated`` headers will default to the created/modified
timestamps of the file, ``Parser`` will be set according to the file extension
(``.html`` -&gt; the html parser in this case), and the ``Url`` will be set
from the filename using this regexp::

	[0-9\-]([a-zA-Z0-9\-._]+)\.[a-z]+	

That is: in this case, the URL will be ``hello_world.html``.

Also, header names are case insensitive.


Building
--------

Generating the static content is as easy as executing the ``express2`` file::

	./express2

It is made executable using the magic hashbang::

	#!/bin/php

If the ``-v`` flag is provided, debugging info will be logged via syslog to
``/var/log/php.log`` (I simply configured syslog to direct ``LOG_LOCAL2``
messages to ``/var/log/php.log``).


Templating
----------

One of my requirements for the Express 2 engine was support for different
templates, to make it possible to give different entries different layouts
(might come in handy). As a plus, it is an adequate separation between logic
and presentation.

As PHP basically is a fancy templating language, and I know it, there is no
need for an extra abstraction like `Smarty <http://www.smarty.net/>`_ or even
`Mustache <http://mustache.github.com/>`_. Including a PHP file with HTML
content interspersed with simple logic and output of variables is sufficient.
(If you want to use a templating language mostly because it has prettier
syntax, get over it.)

So anyway, the whole templating system consists of exactly one function::

	function get_template_content( $filename, $context ) {
		ob_start();
		extract( $context );
		require $filename;
		$c = ob_get_contents();
		ob_end_clean();
		return $c;
	}

If you are unfamiliar with ``extract()``, it means the elements in the
``$context`` array will be available as variables in the template file. This is
illustrated by the ``write_entry()`` function::

	function write_entry( $entry, $filename ) {
		dbg( sprintf( "Writing entry to '%s' ...", $filename ) );

		# write by getting content through template file
		$ctxt = array( 'entry' => $entry );
		file_put_contents( $filename,
			get_template_content( $entry->template, $ctxt ) );
	}

Then, in the template file, the entry content can be displayed like this::

  <h1 class="title main-title">
    <a href="<?= $entry->url ?>" title="Permanent link to this entry."><?= $entry->title ?></a>
  </h1>

  <date class="date"><?= date( 'F d, Y', $entry->created_on ) ?></date>

  <section class="body">
    <?= $entry->body ?>
  </section>


Static data
-----------

Sometimes, it's nice to have an entry with images. Or with a movie, or with any
other type of file. Such things are just static files in the filesystem and are
thus treated as such. They are put in the ``static/`` directory.

Also, any images, CSS and/or JavaScript needed for the templates are treated
the same way.

When building, the entire content of the ``static/`` directory is copied to the
``build/`` directory. So, for example ``static/images/hello.png`` ends up in
``build/images/hello.png``, and ``static/css/screen.css`` in
``build/css/screen.css``.

Plus, if I ever want to add static pages to the blog, I can just put them in
``static/``.

Technically, this copying is easily done by ``rsync``::

	function sync_images( $src_dir, $target_dir ) {
		$cmd = sprintf( 'rsync -r "%s" "%s"', $src_dir, $target_dir );
		dbg( sprintf( "Syncing images using '%s' ...", $cmd ) );
		return system( $cmd );
	}


Implementation details
----------------------

Let's go over some details in ``express2``.


OO vs functions
^^^^^^^^^^^^^^^

Express 2 is not object oriented. Entries are represented by objects, but
contain only data, no behavior, and are manipulated by regular functions.

There is no need for OO here. And in general, mixing data with behavior is
a bad idea (which is exactly what OO does). OO gives encapsulation, but so does
functions + namespaces. Thank you `Rich Hickey
<http://www.mefeedia.com/watch/24169588>`_.

Each core function in Express 2 takes care of a specific task::

	build
	write_entries
	write_entry
	write_atom
	write_archive_page
	sync_images
	get_entries
	read_entry

I think their names explain their functionality. Then there are some helper
functions, of which the most important are::

	get_related_entries
	sort_entries_by_date
	get_template_content

In all, just a handful of functions, as you see. Actually, coming up with
a good set of functions – that is, constructing an adequate abstraction – was
the hardest part, and probably took me twice the time of implementing them.
This is a good thing though: time spent on design is ten times saved in
development. Actually, if you find yourself spending very little time on
design, your design probably sucks and you will pay for it eventually.


Building
^^^^^^^^

Building is done by the ``build()`` function, which is called when executing
``express2``::

	# perform the build process
	build( get_entries( __DIR__ . '/content/' ), __DIR__ . '/build/' );

As straight-forward as can be. This is the ``build()`` function::

	function build( $entries, $target_dir ) {
		dbg( sprintf( "Building to '%s' ...", $target_dir ) );

		$entries = sort_entries_by_date( $entries );

		write_entries( $entries, $target_dir );
		# write latest entry to index.html
		write_entry( $entries[0], path_join( $target_dir, 'index.html' ) );
		sync_images( __DIR__ . '/static/', $target_dir );
		write_atom( $entries, path_join( $target_dir, 'atom.xml' ) );
		write_archive_page( $entries, path_join( $target_dir, 'archive.html' ) );
	}

You can understand largely what is happening by just viewing the source above.
I cannot imagine how it can get much simpler than this.


Well, that was that
-------------------

That's all there is to it, really. *Um, but didn't you say it took two years to
make?* Well, yeah, it only took a couple of days to actually design and build.
But to understand how to build it this simple and not fall in the trap of
over-engineering took me a long time. In comparison, the previous version,
`Express <https://github.com/dfh/express>`_, is like two orders of magnitude
more complex. And unnecessary complexity is a bad, bad thing.
