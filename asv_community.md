# Introduction #

Currently, TextPattern contain 5 hardcoded roles that allow users certain backend functionality, depending on their role. Unfortunately, this presents an issue when attempting to create controlled content on the front end. asv\_community will resolve this using a mixture the current users table and a new table to hold 'members'.

# What is a member? #
A member is visitor of your site that has a unique login that is granted permission to view specific content. A member can be explicitly granted access to view content, or can be associated to a group that has been granted access to the article.

# What is a group? #
A group is a collection of users.

# Uninstalling #
Any article that requires permission to be viewed, will be marked as hidden. Therefore, should the plugin ever be uninstalled or disabled, the content will remain protected.

# Tables #
In order to grant members permissions, the following new tables are required:

asv\_community\_members
{
> id
> name
> email
> username
> password
}

asv\_community\_groups
{
> id
> name
}

asv\_community\_group\_members
{
> id
> member\_id
> group\_id
}

asv\_community\_permissions
{
> id
> article\_id
> type (article, category, section, file, image)
> type\_id (varchar(255))
> level (member, group)
> level\_id
}

  * If accessible to all guests/memebers, an ID of 0 will be used.

# Hierarchy #
Article permissions will trump category permissions, and category permissions will trump section permissions. Therefore you could limit 1 section a specific group but have categories in that section open to them.

# asv\_community\_article #
The original txp:article tag will need to replaced with the new asv\_community\_article tags, which will verify permissions before display the article or article list.

## The process ##
  * call asv\_community\_article()
  * find member id
  * find group id's for member
  * get list of approved categories
  * get list of approved sections
  * SELECT **FROM textpattern WHERE section in (approved\_sections) AND category in (approved\_categories) AND**

# Current Issues #
As it stands right now, all TXP users can see all generated content through the backend. Without modifying the TXP core, there is no way to limit the visibility to them.