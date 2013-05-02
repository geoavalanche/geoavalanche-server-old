=== About ===
name: TMS Layers
website: http://openir.media.mit.edu, http://dukodestudio.com, http://twitter.com/kigen
description: A simple plugin for Ushahidi platform that allows adding TMS Layers as {Base or Overlay} 
version: 0.1
requires: TBD
tested up to: TBD
authors: DuKode, OpenIR, and Seth kigen


== Description ==

A simple plugin for Ushahidi platform that allows adding TMS Layers as {Base or Overlay} 
Based on Seth Kigen's tms plugin.

== Installation ==

1. Copy the entire /Ushahidi-plugin-tms/ directory into your /plugins/ directory.
2. Activate the plugin.
3. Click on plugin settings to add layers.

== Configuration ==

1. Select the [LAYERS] tab 
2. Add all tms based layers that you will need to show on the map
    - Add each layer to it's respective section (base/overlay)
3. Once done with adding the layers navigate back to the [GENERAL] tab
4. Select the type of map setup you will need your maps to appear
    - There are 3 choices
    1. FULL tms -> Allows for full tms support (Both base & overlays will be tms based)
    2. OVERLAY ONLY -> Allows you to add tms based overlays only. The base layer 
                       remains to be the default map as configured Ushahidi map settings
    3. OFF -> Turns off the plugin, allows you to turn off the plugin without having to un-install it. 

== Changelog ==

0.4 
- Added mixed layer support e.g (Google Maps and tms Layers)
- Added configuration interface
- Moved layer configuration to database
- Fixed minor bugs
- Support for Ushahidi 2.4, 2.3 dropped.

0.3
- Added 2.5 support

0.2
- Added 2.3 and 2.4 support. 
- Fixed projection issues

