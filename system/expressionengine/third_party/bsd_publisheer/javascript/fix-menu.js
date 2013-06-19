;(function($){
var $publishMenu = $("#navigationTabs a:contains(\"Publish\") + ul").attr("id", "publish-menu").addClass("clearfix");
$channels = $publishMenu.children("li").addClass("channels").detach();
$("#publish-menu").prepend("<li id=\"listjs-search-li\"><label id=\"listjs-search-label\" for=\"list-js-search\">Channel Search<input id=\"listjs-search\" type=\"text\" class=\"search\" placeholder=\"Channel Name\"/></label></li>");
$publishMenu.append("<li id=\"channel-list\"><ul class=\"listjs\"></ul></li>");
$channels.appendTo("#channel-list ul.listjs").children("a").addClass("channel-name");
var options = { valueNames: ["channel-name"], listClass: "listjs" };
var channelList = new List("publish-menu", options);
})(jQuery);