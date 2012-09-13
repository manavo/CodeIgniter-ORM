# CodeIgniter - SmartModel

## What is this?

After trying out several PHP MVC frameworks (including writing my own little one), I decided that I'd probably stick with CodeIgniter overall. However, I did like a few things from CakePHP, so my aim here is to create a base model which provides some of that functionality.

## Is this production ready?

Well, I haven't used it in production anywhere yet, so proooooobably not. However, it does all seem to work so far, so do feel free to try it out!

## Cool, so what does this actually give me?

Well, it gives you an easy way use your model to load, create, update or delete rows in your database. It also gives you an easy way of adding relationships between your models (many-to-many relationships, I think people also call this HasAndBelongsToMany [HABTM]).

## Installation instructions

Just copy this file to your ```/application/core/``` directory, and make your models extend MY_Model instead of CI_Model.

### What if I already have a MY_Model class?

Well, that makes things a bit more awkward, but still possible! Simplest way (although might not be the best) would probably be to rename this class to Smart_Model, append it to the bottom of the MY_Model file. Then make the MY_Model class that you have extend Smart_Model.

I haven't tested this, but should theoretically work!

## Show me the basics

We'll go under the assumption of a blog site, where we're creating posts. Your model here would be (you guessed it!): __Post__.

However, before we jump in, we should explain some conventions:

### Conventions for models

Name your model the singular of the object it describes, and the table the plural of what it describes. So, model of __post__ equals a table of __posts__. Model of __diary__ equals a table of __diaries__.

If you want to name your table something else, make sure you override the

```php
protected $_table = null;
```

property of the SmartModel to whatever you want (posts, postings, bananas, etc) in your new model. Your table should be __lowercase__ with __underscores__ as a separator.

Name your primary key database field __id__.

If it's something else, you should override the

```php
protected $_primary_key = 'id';
```

property of the SmartModel to whatever you want (post_id, user_id, etc) in your new model.

### Examples

So, here are some simple examples:

### Create new entry

```php
// Assuming our posts table has only 3 columns: id, title and text
$this->post->create();
$this->post->title = 'Catchy title';
$this->post->text = 'All your text goes here';
$this->post->save();
```

You've just created a new entry. Pat yourself on the back, and continue reading.

### Load an entry
```php
// Assuming our posts table has only 3 columns: id, title and text
$this->post->load(1); // 1 is just an example ID of a post
echo '<h1>'.$this->post->title.'</h1>';
echo '<p>'.$this->post->text.'</p>';
```

Simple as that!

Note that the load method returns a boolean which tells us if the loading was successful.

### Update an entry

```php
// Assuming our posts table has only 3 columns: id, title and text
$this->post->load(1); // 1 is just an example ID of a post
$this->post->title = 'Edited title!';
$this->post->save();
```
### Delete an entry

```php
$this->post->load(1); // 1 is just an example ID of a post
$this->post->delete();
```

That pretty much covers the basics!

## How about those relationships?

Again, let's start with some conventions!

### Conventions for One-To-Many relationships

Traditionally, one-to-many relationships are crated by adding an extra column in one of the tables, referencing the primary key of the other table. For example, on a posts table, we will put a user_id column to show which user this posts belongs to.

So following this, the SmartModel requires that we put a column named after the model (singular), followed by "\_id", into the table. Just like the example above.

### Conventions for Many-To-Many relationships

Traditionally, many-to-many relationships are created in a new table, including the IDs of the 2 other tables you're referencing. No exception here, we do that. But there are a few things you have to be specific about.

Following the table naming convention from the models section, we'll have tables such as users, posts, diaries, tags or categories. So, taking the 2 tables we'll be joining (tags and posts seem like a good example), your relationship table will be __posts_tags__. This is calculated by taking the 2 models we're joining, taking their __plural__, joining them by an __underscore__, in __alphabetical order__. (So the table will be __posts_tags__, _NOT_ ~~tags_posts~~)

The columns you'll add which will be the foreign keys, should be your model name (singular), followed by "\_id". So for our example above, we'd have at least 2 columns, __post_id__ and __tag_id__.

Also, don't forget to make the id fields foreign keys to their respective tables. And make a primary key out of the 2 columns combined.

### Add a Many-To-Many relationship (assuming the posts and tags already exist)

```php
$this->post->load(1); // 1 is just an example ID of a post
$this->post->add_tag(3); // 3 is just an example ID of a tag
```

So basically what we do, is run on our model the method add_{other model name}. SmartModel looks at the name of the other model, finds the table, finds the relationship table, and adds the entry.

Note that this could be done the other way around as well (add the post to the tag).

```php
$this->tag->load(3); // 3 is just an example ID of a tag
$this->tag->add_post(1); // 1 is just an example ID of a post
```

### Remove a Many-To-Many relationship

```php
$this->post->load(1); // 1 is just an example ID of a post
$this->post->remove_tag(3); // 3 is just an example ID of a tag
```

As above, we just call remove_{other model name}, and it disappears!

### Check existence of a Many-To-Many relationship

```php
$this->post->load(1); // 1 is just an example ID of a post
if ($this->post->has_tag(3) == true) { // 3 is just an example ID of a tag
    echo 'Has tag';
} else {
    echo "Doesn't have tag";
}
```

As above, we just call has_{other model name}, and it gives you a boolean telling you if it exists or not.

### Get items of a Many-To-Many relationship

```php
$this->post->load(1); // 1 is just an example ID of a post
$tags = $this->post->get_tags(Tag::$MANY_TO_MANY);
```

This will give you the rows of the relationship table returned, just as CodeIgniter's Database class returns them (the result() method, not the result_array() method).

The ```$MANY_TO_MANY``` constant could be referenced by any of the following:

```php
Tag::$MANY_TO_MANY
Post::$MANY_TO_MANY
MY_Model::$MANY_TO_MANY
```

### Get items of a One-To-Many relationship

```php
// Assuming here the posts table has a user_id
$this->user->load(1); // 1 is just an example ID of a post
$posts = $this->user->get_posts(Post::$ONE_TO_MANY);
```

This will give you the rows of the other table returned (posts table in this case), just as CodeIgniter's Database class returns them (the result() method, not the result_array() method).

The ```$ONE_TO_MANY``` constant could be referenced by any of the following:

```php
User::$ONE_TO_MANY
Post::$ONE_TO_MANY
MY_Model::$ONE_TO_MANY
```

## Slightly fancier than the basics

### Creating a new object from POST data

When you post a form to create a new object, you'll probably have to write the following line several times:

```php
$this->model->create(); // reset the model to be empty
$this->model->var1 = $this->input->post('var1');
$this->model->var2 = $this->input->post('var2');
$this->model->var3 = $this->input->post('var3');
```

To save you the hassle of doing this, SmartModel allows you to pass an array of POST variable names into the ```create()``` method. So instead of the code above, you can simply do the following:

```php
$this->model->create(array('var1', 'var2', 'var3')); // reset the model to be empty, and then read var1, var2 and var3
```

which will have exactly the same result.

### ```load_by_{field}``` methods

A simple way of loading a model by a different field, is to call the magic ```load_by_{field}``` methods. As long as the field you are using is unique (obviously), you'll be able to use this to load a model. Popular examples would be the following:

```php
$this->user->load_by_email('email@domain.com');
// OR
$this->user->load_by_username('manavo');
```

Then your model (user model in the example above) will be loaded in the same way as if you had run ```$this->user->load(1);```.

Note that just as the ```load()``` method, the ```load_by_{field}``` methods also return a boolean which tells us if the loading was successful.

## Wow, does it get any fancier than that?

Why yes, yes it does! However, I haven't written the documentation yet. The things remaining to be documented are:

* extra fields on relationships
* magic timestamps

I'll get around to documenting those and commenting the code soon. Until then, you can have a go at reading through the code and seeing how those work!

## License

The MIT License

Copyright (c) 2012, Philip Manavopoulos <@manavo>

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.