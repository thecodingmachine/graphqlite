module.exports={
  "title": "GraphQLite",
  "tagline": "GraphQL in PHP made easy",
  "url": "https://graphqlite.thecodingmachine.io",
  "baseUrl": "/",
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
        }
      }
    ]
  ],
  "plugins": [],
  "themeConfig": {
    "navbar": {
      "title": "GraphQLite",
      "logo": {
        "src": "/img/graphqlite.svg"
      },
      "style": "dark",
      "items": [
        {
          "href": "https://github.com/thecodingmachine/graphqlite",
          "label": "GitHub",
          "position": "right"
        },
        {
          "label": "Version",
          "to": "docs",
          "position": "right",
          "items": [
            {
              "label": "4.1",
              "to": "docs/",
              "activeBaseRegex": "docs/(?!3.0|4.0|4.1|next)"
            },
            {
              "label": "4.0",
              "to": "docs/4.0/"
            },
            {
              "label": "3.0",
              "to": "docs/3.0/"
            },
            {
              "label": "Main/Unreleased",
              "to": "docs/next/",
              "activeBaseRegex": "docs/next/(?!support|team|resources)"
            }
          ]
        }
      ]
    },
    "prism": {
      "theme": require('prism-react-renderer/themes/oceanicNext'),
      "additionalLanguages": ['php']
    },
    "image": "/img/graphqlite.svg",
    "footer": {
      "links": [],
      "logo": {
        "src": "/img/graphqlite.svg",
        "href": 'https://github.com/thecodingmachine/graphqlite',
      }
    },
    "algolia": {
      "apiKey": "8fcce617e281864dc03c68d17f6206db",
      "indexName": "graphqlite_thecodingmachine",
      "algoliaOptions": {}
    },
    "gtag": {
      "trackingID": "UA-10196481-8"
    }
  }
}
