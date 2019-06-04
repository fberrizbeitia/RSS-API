/* -----------------------------------------------------------------------
Dependencies: Jquery. This class requires jquery to work

LICENCE

Copyright (c) 2016, Francisco Berrizbeitia and Tomasz Neugebauer
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:

* Redistributions of source code must retain the above copyright notice, this
  list of conditions and the following disclaimer.

* Redistributions in binary form must reproduce the above copyright notice,
  this list of conditions and the following disclaimer in the documentation
  and/or other materials provided with the distribution.

* Neither the name of RSSapi nor the names of its
  contributors may be used to endorse or promote products derived from
  this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

-------------------------------------------------------------------------*/

function rssItem(in_title,in_description,in_outlink,in_pubdate,in_mediaURL,in_mediaType){
	
	if(in_title !== undefined){
		this.title = in_title;
	}else{
		this.title = '';
	}
	
	if(in_description !== undefined){
		this.description = in_description;
	}else{
		this.description = '';
	}
	
	if(in_outlink !== undefined){
		this.outlink = 	in_outlink;
	}else{
		this.outlink = '';
	}
	
	if(in_pubdate !== undefined){
		this.pubdate = in_pubdate;
	}else{
		this.pubdate = '';
	}
	
	if(in_mediaURL !== undefined){
		this.mediaURL = in_mediaURL;
	}else{
		this.mediaURL = '';
	}
	
	if(in_mediaType !== undefined){
		this.mediaType = in_mediaType;
	}else{
		this.mediaType = '';
	}
}

function rss(){
	var itemsList = [];
	
	/* ----------------------------------------------------------------------------------
		Substitute [YOUR DOMAIN] with the proper value
	-----------------------------------------------------------------------------------*/
	var serviceURL = 'http://[YOUR DOMAIN]/service/RSSget.php';
	var loaderURL = 'http://[YOUR DOMAIN]/lib/loading_50.gif';
	
	
	this.setItems = setItems;
	this.loadFeed = loadFeed;
	this.getItemsList = getItemsList;
	this.numItems = numItems;
	this.getItem = getItem;
	this.sortByTitle = sortByTitle;
	this.sortByDate = sortByDate;
	this.displayAsinc = displayAsinc;
	
	function numItems(){
		return itemsList.length;
	}
	
	function getItem(index){
		return itemsList[index];
	}
	
	function sortByTitle(){
		itemsList.sort(function(a, b){
			var titleA=a.title.toLowerCase();
			var titleB=b.title.toLowerCase();
			if (titleA< titleB){
				 return -1 ;
			}
			if (titleA> titleB){
				return 1;
			}
			 return 0
			})//function(a, b)
			
	}
	
	function sortByDate(){
		itemsList.sort(function(a, b){
			
			var dateA=new Date (a.publishedDate);
			var dateB=new Date (b.publishedDate);
			
			if (dateA < dateB){
				 return -1 ;
			}
			if (dateA > dateB){
				return 1;
			}
			 return 0
			})//function(a, b)
	}
	
	function loadFeed (in_url, in_sortingField, in_sortingType, in_sortingOrder, in_numElements){
		$.ajax({
			url: serviceURL,
			data: {
				feedUrl: encodeURI(in_url),
				sortField: in_sortingField,
				sortOrder: in_sortingOrder,
				sortType: in_sortingType,
				limit: in_numElements
			},
			
			type: 'POST',
			crossDomain: true,
			
			success: function(data){
				setItems(data)			
			},
			
			error: function( xhr, status, errorThrown ) {
				console.log( "Sorry, there was a problem!" );
			},	
			async:false,	
		});
	}
	
	
	function setItems(data){
		//console.log(data);
		var entries = JSON.parse(data);
				
		for(var i = 0; i < entries.length; i++){
		//	console.log(entries[i].media);
			itemsList.push(new rssItem(entries[i].title,entries[i].description,entries[i].link,entries[i].pubdate,entries[i].media.url,entries[i].media.type));
		}

	}
	
	function getItemsList(){
		return (itemsList);
	}
	
	function createSummary(text,numChars){
		var result = '';
		var word = text.split(" ");
		var index = 0;
		if( word.length > 1){
			while (result.length < numChars  && index < word.length){
				result = result + " " + word[index];
				index++;
			}
			if(index < word.length){
				result = result + " ...";
			}
		}	
		return result; 
		
	}
	
	/*
	This function will asincronicaly fetch the feed and create all the DOM elements to display the feed
	PARAMS: 
		in_url: feed URL
		in_containerID : ID of the container where items are going to be created
		in_sortingField: to sort elements by title, date or none
		in_sortingOrder: sort ASCending or DESCending order
		in_sortingType: Sorting field data type: Numeric, String or Date
		in_numElements: NUmber of elements to be displayed
		in_descriptionLength: the max numer of characters to display in the description
		in_showimages: (1,0) or (True,False) whether to show images if available.
	*/
	
	function displayAsinc(in_url, in_containerID, in_sortingField, in_sortingType, in_sortingOrder, in_numElements, in_descriptionLength, in_showimages){
		
		if( (in_url === undefined) || (in_containerID === undefined) ){
			return false;
		}
		
		if(in_showimages === undefined){
			var showimages = false;
		}else{
			if(in_showimages == 'true'){
				var showimages = true;
			}else{
				var showimages = false;
			}
			
		}
		
		/*  SHOW THE LOADER GIF  */
		var img = document.createElement('img');
		img.src = loaderURL;
		var randomid = 'rssLoader'+Math.floor(Math.random() * 1000);
		img.id = randomid;
		
		//console.log(in_containerID);
		document.getElementById(in_containerID).appendChild(img);

		$.ajax({
			url: serviceURL,
			data: {
				feedUrl: encodeURI(in_url),
				sortField: in_sortingField,
				sortOrder: in_sortingOrder,
				sortType: in_sortingType,
				limit: in_numElements
			},
			
			type: 'POST',
			crossDomain: true,
			
			success: function(data){
				
				var loader = document.getElementById(randomid);
				loader.parentNode.removeChild(loader);
				
				//console.log(data);
				setItems(data);
				
				var numElements = itemsList.length;
								
				if(( in_numElements !== undefined) && (in_numElements < itemsList.length) ){
					numElements = in_numElements;
				}
				
				//console.log(numElements);
				
				if(numElements > 0){
					
					var container = document.getElementById(in_containerID);
					var ulNode = document.createElement('ul');
	
					for(var i = 0; i < numElements; i++){
						var li = document.createElement('li');
						var ahref = document.createElement('a');
							ahref.setAttribute('href',itemsList[i].outlink);
							ahref.setAttribute('target','_blank');
							ahref.appendChild(document.createTextNode(itemsList[i].title));
						li.appendChild(ahref);
						li.appendChild(document.createElement('br'));
						
							var table = document.createElement('table');
							table.setAttribute('cellpadding','5');
								var tr = document.createElement('tr')
									var tdImg = document.createElement('td');
									tdImg.setAttribute('valign',"top");
										if(showimages && itemsList[i].mediaURL != '' ){
											var itemImg = document.createElement('img');
											itemImg.src = itemsList[i].mediaURL;
											itemImg.width = "150";
											tdImg.appendChild(itemImg);	
										}
									var tdDescription = document.createElement('td');
									tdDescription.setAttribute('valign',"top");
									tdDescription.appendChild(document.createTextNode(createSummary(itemsList[i].description,in_descriptionLength)));	
								tr.appendChild(tdImg);
								tr.appendChild(tdDescription);
							table.appendChild(tr);
						li.appendChild(table);	
						
						ulNode.appendChild(li);
					}
					container.appendChild(ulNode);
					
				}
						
			},
			
			error: function( xhr, status, errorThrown ) {
				console.log( "Sorry, there was a problem!" );
				console.log(errorThrown);
				console.log(status);
				console.log(xhr.responseText);
			},	

		});
				
	}//function displayAsinc(in_url, in_containerID, in_sorting, in_numElements, in_descriptionLength){
	
}
