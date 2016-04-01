-----------------------------------------------------------------------------------

JWPlayer is NOT bundled with this distribution.
You'll need to download JWPlayer on your own:
<http://www.longtailvideo.com/>

Once you've downloaded JWPlayer,
place three of its files in this directory:

1. jwplayer.js
2. player.swf
3. yt.swf

- That's it. You don't need swfobject.js.

-----------------------------------------------------------------------------------

If JWPlayer is NOT present, FlowPlayer will be used as an automatic alternative.
~ In other words, you don't have to install JWPlayer. It's optional.
<http://www.longtailvideo.com/>, <http://flowplayer.org/>

If you install JWPlayer, and you would like to use a custom skin file,
you can place the [skin].zip file inside of the /skins/ directory.

Then, in your Shortcode, add this attribute: skin="[skin file name]"
 ( just the name of the zip file, WITHOUT the zip file extension )
 Example: skin="eleganttwilight"