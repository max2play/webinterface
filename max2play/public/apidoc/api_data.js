define({ "api": [
  {
    "type": "post",
    "url": "/plugins/max2play_settings/controller/Squeezeplayer.php",
    "title": "Equalizer Settings",
    "name": "Equalizer_Settings",
    "group": "Audioplayer_API",
    "version": "1.0.0",
    "description": "<p>Fetch / Update / Reset Equalizer Settings on Max2Play Device.<br /></p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "apijson",
            "description": "<p>Always set to &quot;1&quot; when using the API</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "action",
            "description": "<p>Define what to do: may be empty or &quot;saveEqualizer&quot; or &quot;resetEqualizer&quot;</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "use_equalizer",
            "description": "<p>Override Max2Play Setting wether to read Equalizer settings: must be set to &quot;1&quot;, if Equalizer is disabled in web interface</p>"
          },
          {
            "group": "Parameter",
            "type": "Array",
            "optional": false,
            "field": "settingsEqualizer",
            "description": "<p>Array to set new Equalizer Values for each frequency: Key =&gt; Value: [01.+31+Hz]=54</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "Array",
            "optional": false,
            "field": "equalvalues",
            "description": "<p>Object of Type Equalizer</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\nContent-Type: application/json; charset=utf-8\nX-API-Version: 1.0.0\n{\n  ...\n  \"equalvalues\":{\n\t\t\"01. 31 Hz\":\"55%\",\n\t\t\"02. 63 Hz\":\"60%\",\n\t\t\"03. 125 Hz\":\"66%\",\n\t\t\"04. 250 Hz\":\"66%\",\n\t\t\"05. 500 Hz\":\"66%\",\n\t\t\"06. 1 kHz\":\"70%\",\n\t\t\"07. 2 kHz\":\"66%\",\n\t\t\"08. 4 kHz\":\"66%\",\n\t\t\"09. 8 kHz\":\"66%\",\n\t\t\"10. 16 kHz\":\"66%\"\n\t },\n  ...\n}",
          "type": "json"
        }
      ]
    },
    "examples": [
      {
        "title": "Example get Equalizer Values:",
        "content": "curl -v -X GET \"http://max2play/plugins/max2play_settings/controller/Squeezeplayer.php?apijson=1&use_equalizer=1\"",
        "type": "json"
      },
      {
        "title": "Example change Equalizer Values (GET):",
        "content": "curl -v -X GET \"http://max2play/plugins/max2play_settings/controller/Squeezeplayer.php?apijson=1&use_equalizer=1&action=saveEqualizer&settingsEqualizer[01.+31+Hz]=54&settingsEqualizer[02.+63+Hz]=66%25&settingsEqualizer[03.+125+Hz]=66%25&settingsEqualizer[04.+250+Hz]=66%25&settingsEqualizer[05.+500+Hz]=66%25&settingsEqualizer[06.+1+kHz]=66%25&settingsEqualizer[07.+2+kHz]=66%25&settingsEqualizer[08.+4+kHz]=66%25&settingsEqualizer[09.+8+kHz]=66%25&settingsEqualizer[10.+16+kHz]=66%25",
        "type": "json"
      },
      {
        "title": "Example reset Equalizer Values (GET):",
        "content": "curl -v -X GET \"http://max2play/plugins/max2play_settings/controller/Squeezeplayer.php?apijson=1&use_equalizer=1&action=resetEqualizer\"",
        "type": "json"
      }
    ],
    "filename": "../../application/plugins/max2play_settings/controller/Squeezeplayer.php",
    "groupTitle": "Audioplayer_API"
  }
] });
