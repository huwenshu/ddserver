/**
 * Created by Common on 14-9-12.
 */
var deviceAgent = navigator.userAgent.toLowerCase();
var agentID = deviceAgent.match(/(iphone|ipod|ipad|android)/) || '';
var mobile = {
    iphone:agentID.indexOf("iphone")>=0
    ,android:agentID.indexOf("android")>=0
    ,ipad:agentID.indexOf("ipad")>=0
    ,ipad:agentID.indexOf("ipod")>=0
}
if(mobile.iphone){
    //alert('iphone');
    document.write('<meta name="viewport" content="width=device-width, initial-scale=01, user-scalable=0,minimal-ui">');
}else if(mobile.android){
    //alert('android');
    document.write('<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=0">');
    //document.write('<meta name="viewport" content="target-densitydpi=device-dpi">');
}else{
    //alert('other');
    document.write('<meta name="viewport" content="width=device-width, initial-scale=0.5, user-scalable=0,minimal-ui">');
}