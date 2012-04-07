# CodeIgniter - SmartModel

## What is this?

After trying out several PHP MVC frameworks (including writing my own little one), I decided that I'd probably stick with CodeIgniter overall. However, I did like a few things from CakePHP, so my aim here is to create a base model which provides some of that functionality.

## Is this production ready?

Well, I haven't used it in production anywhere yet, so proooooobably not. However, it does all seem to work so far, so do feel free to try it out!

## Cool, so what does this actually give me?

Well, it gives you an easy way use your model to load, create, update or delete rows in your database. It also gives you an easy way of adding relationships between your models (many-to-many relationships, I think people also call this HasAndBelongsToMany [HABTM]).

## Installation instructions

Just copy this file to your ```/application/core/``` directory, and make your models extend MY_Model instead of CI_Model.

### What if I already have a MY_Model function?

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

## How about those relationships

Again, let's start with some conventions!

### Conventions for relationships

Traditionally, many-to-many relationships are created in a new table, including the IDs of the 2 other tables you're referencing. No exception here, we do that. But there are a few things you have to be specific about.

Following the table naming convention from the models section, we'll have tables such as users, posts, diaries, tags or categories. So, taking the 2 tables we'll be joining (tags and posts seem like a good example), your relationship table will be __posts_tags__. This is calculated by taking the 2 models we're joining, taking their __plural__, joining them by an __underscore__, in __alphabetical order__. (So the table will be __posts_tags__, _NOT_ ~~tags_posts~~)

The columns you'll add which will be the foreign keys, should be your model name (singular), followed by "\_id". So for our example above, we'd have at least 2 columns, __post_id__ and __tag_id__.

Also, don't forget to make the id fields foreign keys to their respective tables. And make a primary key out of the 2 columns combined.

### Add a relationship (assuming the posts and tags already exist)

```php
$this->post->load(1); // 1 is just an example ID of a post
$this->post->add_tag(3); // 3 is just an example ID of a tag
```

So basically what we do, is run on our model the function add_{other model name}. SmartModel looks at the name of the other model, finds the table, finds the relationship table, and adds the entry.

Note that this could be done the other way around as well (add the post to the tag).

```php
$this->tag->load(3); // 3 is just an example ID of a tag
$this->tag->add_post(1); // 1 is just an example ID of a post
```

### Remove a relationship

```php
$this->post->load(1); // 1 is just an example ID of a post
$this->post->remove_tag(3); // 3 is just an example ID of a tag
```

As above, we just call remove_{other model name}, and it disappears!

### Check existence of a relationship

```php
$this->post->load(1); // 1 is just an example ID of a post
if ($this->post->has_tag(3) == true) { // 3 is just an example ID of a tag
    echo 'Has tag';
} else {
    echo "Doesn't have tag";
}
```

As above, we just call has_{other model name}, and it gives you a boolean telling you if it exists or not.

### Get items of relationship

```php
$this->post->load(1); // 1 is just an example ID of a post
$tags = $this->post->get_tags();
```

This will give you the rows of the relationship table returned, just as CodeIgniter's Database class returns them (the result() function, not the result_array() function).

## Wow, does it get any fancier than that?

Why yes, yes it does! However, I haven't written the documentation yet. The things remaining to be documented are:

* load_by_{field}
* extra fields on relationships
* magic timestamps

I'll get around to documenting those and commenting the code soon. Until then, you can have a go at reading through the code and seeing how those work!