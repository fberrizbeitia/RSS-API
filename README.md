# Description
This API was developed to replace the Google feed API that’s been officially deprecated. The main purpose of this development is to provide the means to consume and process RSS feeds using javascript only. This cannot be made using straight javascript because of cross domain restrictions. To overcome this the API uses cross-origin resource sharing (CORS), allowing access to calls from a domain white list. 

Besides implementing the CORS mechanism the system also caches the feeds to improve reliability and performance. The locally stored files are preprocessed to clean up the non-standard tags and sort item on the given criteria. An index of all cached is maintained. Once the feed is included in the index a timed script (cron job) refreshes the content regularly.

Log files for both feed updates and access are maintain also to allow in depth analysis on the use and performance of the system.

# Installation and Configuration

To install the service copy all files to a directory in your web server, ideally with its own domain. The service and cache folder and subfolders must have writing permission.
 
In the service folder open config.php, add the allowed domains and the base url for the feeds folder. When adding domains is important to notice that subdomains must be added explicitly to work, the url must include the protocol (http or https) if a domain may access the feeds using either both entries must be included. There’s no limit to the number of allowed domains.

In the lib folder open the feed.js file and set the proper values for the serviceURL and the loaderURL. For example:

```javascript
function rss(){
var itemsList = [];
	var serviceURL = 'https://rssapi.example.com/service/RSSget.php';
	var loaderURL = 'https://rssapi.example.com/lib/loading_50.gif';
…
```
# Usage

For CORS security reasons, before using the API, the domain and/or subdomains where the /lib/feed.js is included must be whitelisted in the server. 

1.	Include jQuery.
2.	Include the properly configured feed.js file
	```html
	<script src=" https://rssapi.example.com/lib/feed.js "></script>
	```
3.	Create the object: 
	```javascript
	var objRss = new rss(); 
	```
4.	Make a call to the displayAsinc function:
	objRss.displayAsinc(in_url, in_containerID, in_sorting,in_type, in_sorting_order,in_count,in_descriptionLength);
	```javascript
	objRss.displayAsinc(feed,spanId, 'pubDate','date','desc',count, 512);
	```

The library provides to ways of dealing with the retrieval and display of the feed: The asynchronous method, as the one show in the example above and set of method for loading and manipulating the feed.

The displayAsinc functions receives the following parameters:
* in_url: feed URL
* in_containerID : ID of the container where items are going to be created (DOM elements such as DIV)
* in_sorting: to sort elements by, for example, title, date (pubdate, dc:date). The function can accept any tag present inside the item tag.
* in_type: The variable type of the sorting field: date, numerical or string
* in_sorting_order:  Either desc or asc.
* In_count: The max number of <items> to display.
* in_descriptionLength: the max number of characters to display in the description

The function will automatically create all DOM elements to display the feed nested inside the provided container.

# Documentation

Click [here](documentation.pdf) for a more detailed documentation.

# About and Contact

The RSS API was developed at Concordia University Library as an ongoing academic effort. You can contact the current maintainers by email at francisco[dot]berrizbeitia[at]concordia[dot]ca

We strongly encourage you to please report any issues you have with RSS API. You can do that over our contact email or creating a new issue here on Github.

francisco[dot]berrizbeitia[at]concordia[dot]ca

tomasz[dot]neugebauer[at]concordia[dot]ca

# License

[BSD 3-Clause License](LICENSE). 

# Acknowledgments

atom2rss.xsl by [pornel](https://github.com/pornel/atom2rss) under [CC license](http://creativecommons.org/licenses/by/2.5/) 

