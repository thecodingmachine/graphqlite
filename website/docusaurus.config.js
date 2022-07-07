const lightCodeTheme = require('prism-react-renderer/themes/github');
const darkCodeTheme = require('prism-react-renderer/themes/oceanicNext');

module.exports={
  "title": "GraphQLite",
  "tagline": "GraphQL in PHP made easy",
  "url": "https://graphqlite.thecodingmachine.io",
  "baseUrl": "/",
  "trailingSlash": false,
  "organizationName": "thecodingmachine",
  "projectName": "graphqlite",
  "scripts": [
    "https://buttons.github.io/buttons.js",
    "https://unpkg.com/mermaid@8.0.0/dist/mermaid.min.js"
  ],
  "favicon": "/img/logo.svg",
  "customFields": {},
  "onBrokenLinks": "log",
  "onBrokenMarkdownLinks": "log",
  "presets": [
    [
      "@docusaurus/preset-classic",
      {
        "docs": {
          "showLastUpdateAuthor": true,
          "showLastUpdateTime": true,
          "editUrl": "https://github.com/thecodingmachine/graphqlite/edit/master/website/",
          "path": "../website/docs",
          "remarkPlugins": [
            require('mdx-mermaid'),
            {
              mermaid: {
                theme: 'forest',
                logLevel: 3,
                flowchart: { curve: 'linear' },
                gantt: { axisFormat: '%m/%d/%Y' },
                sequence: { actorMargin: 50 },
              }
            }
          ],
          "sidebarPath": "./sidebars.json"
        },
        "blog": {},
        "theme": {
          "customCss": "/src/css/custom.css"
        },
        "gtag": {
          "trackingID": "UA-10196481-8"
        }
      }
    ]
  ],
  "plugins": [],
  "themeConfig": {
    "navbar": {
      "title": "GraphQLite",
      "logo": {
        "src": "/img/graphqlite.svg",
        "href": "/"
      },
      "style": "dark",
      "items": [
        {
          "href": "https://github.com/thecodingmachine/graphqlite",
          "label": "GitHub",
          "position": "right"
        },
        {
          "type": 'docsVersionDropdown',
          "position": "right",
          "dropdownActiveClassDisabled": true
        },
      ]
    },
    "prism": {
      "theme": lightCodeTheme,
      "darkTheme": darkCodeTheme,
      "additionalLanguages": ['php']
    },
    "algolia": {
      "appId": "14EJC4HSQA",
      "apiKey": "14e7cb570aceaf9dbcee76af8e701d1b",
      "indexName": "graphqlite_thecodingmachine",
    },
    "image": "/img/graphqlite.svg",
    "footer": {
      "links": [],
      "logo": {
        "src": "/img/graphqlite.svg",
        "href": 'https://github.com/thecodingmachine/graphqlite',
      }
    }
  }
}
