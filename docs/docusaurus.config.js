// @ts-check
// `@type` JSDoc annotations allow editor autocompletion and type checking
// (when paired with `@ts-check`).
// There are various equivalent ways to declare your Docusaurus config.
// See: https://docusaurus.io/docs/api/docusaurus-config

import {themes as prismThemes} from 'prism-react-renderer';

// This runs in Node.js - Don't use client-side code here (browser APIs, JSX...)

/** @type {import('@docusaurus/types').Config} */
const config = {
  title: 'Eloquent Tables',
  tagline: 'Eloquent Tables for your Laravel Application',
  favicon: 'img/logo_transparent_small.png',

  // Future flags, see https://docusaurus.io/docs/api/docusaurus-config#future
  future: {
    v4: true, // Improve compatibility with the upcoming Docusaurus v4
  },

  // Set the production url of your site here
  url: 'https://bricknpc.github.io',
  // Set the /<baseUrl>/ pathname under which your site is served
  // For GitHub pages deployment, it is often '/<projectName>/'
  baseUrl: '/eloquent-tables',

  // GitHub pages deployment config.
  // If you aren't using GitHub pages, you don't need these.
  organizationName: 'bricknpc', // Usually your GitHub org/user name.
  projectName: 'eloquent-tables', // Usually your repo name.

  onBrokenLinks: 'throw',

  markdown: {
    mermaid: true,
    // `.md` is parsed as CommonMark and only `.mdx` gets MDX. Agent plans are written
    // by a tool and routinely contain angle brackets and braces outside code fences,
    // which MDX would try to parse as JSX and fail the build on.
    format: 'detect',
  },

  themes: ['@docusaurus/theme-mermaid'],

  // Even if you don't use internationalization, you can use this field to set
  // useful metadata like html lang. For example, if your site is Chinese, you
  // may want to replace "en" with "zh-Hans".
  i18n: {
    defaultLocale: 'en',
    locales: ['en'],
  },

  presets: [
    [
      'classic',
      /** @type {import('@docusaurus/preset-classic').Options} */
      ({
        docs: {
          sidebarPath: './sidebars.js',
          // "HEAD" resolves to whichever branch is currently the default, which is the
          // current major version branch. Naming it that way means these links survive the
          // next major taking over as default. Remove editUrl to drop "edit this page".
          editUrl:
            'https://github.com/bricknpc/eloquent-tables/tree/HEAD/docs/',
        },
        blog: {
          showReadingTime: true,
          feedOptions: {
            type: ['rss', 'atom'],
            xslt: true,
          },
          // Please change this to your repo.
          // Remove this to remove the "edit this page" links.
          // editUrl:
          //   'https://github.com/facebook/docusaurus/tree/main/packages/create-docusaurus/templates/shared/',
          // Useful options to enforce blogging best practices
          onInlineTags: 'warn',
          onInlineAuthors: 'warn',
          onUntruncatedBlogPosts: 'warn',
        },
        theme: {
          customCss: './src/css/custom.css',
        },
        gtag: {
            trackingID: 'G-GGHJT4LLJ2',
            anonymizeIP: true,
        }
      }),
    ],
  ],

  themeConfig:
    /** @type {import('@docusaurus/preset-classic').ThemeConfig} */
    ({
      // Replace with your project's social card
      image: 'img/docusaurus-social-card.jpg',
      colorMode: {
        respectPrefersColorScheme: true,
      },
      navbar: {
        title: 'Eloquent Tables',
        logo: {
          alt: 'Eloquent Tables Logo',
          src: 'img/logo_transparent_small.png',
        },
        items: [
          {
            type: 'docSidebar',
            sidebarId: 'tutorialSidebar',
            position: 'left',
            label: 'Documentation',
          },
          {
            type: 'docSidebar',
            sidebarId: 'plansSidebar',
            docsPluginId: 'plans',
            position: 'left',
            label: 'Agent Plans',
          },
          {to: '/blog', label: 'Blog', position: 'left'},
          {to: '/showcase', label: 'Community Showcase', position: 'left'},
          {
            href: 'https://github.com/bricknpc/eloquent-tables',
            label: 'GitHub',
            position: 'right',
          },
        ],
      },
      footer: {
        style: 'dark',
        links: [
          {
            title: 'Docs',
            items: [
              {
                label: 'Documentation',
                to: '/docs/intro',
              },
            ],
          },
          {
            title: 'Community',
            items: [
              {
                label: 'Youtube',
                href: 'https://youtube.com/@bricknpc-codes',
              },
              {
                label: 'Showcase',
                href: '/showcase',
              },
            ],
          },
          {
            title: 'More',
            items: [
              {
                label: 'Agent Plans',
                to: '/plans',
              },
              {
                label: 'Blog',
                to: '/blog',
              },
              {
                label: 'GitHub',
                href: 'https://github.com/bricknpc/eloquent-tables',
              },
            ],
          },
        ],
        copyright: `Copyright © ${new Date().getFullYear()} BrickNPC. Built with Docusaurus.`,
      },
      prism: {
        theme: prismThemes.github,
        darkTheme: prismThemes.dracula,
        additionalLanguages: ["php", "bash"]
      },
    }),
    plugins: [
      [
        '@docusaurus/plugin-content-docs',
        /** @type {import('@docusaurus/plugin-content-docs').Options} */
        ({
          id: 'plans',
          path: 'plans',
          routeBasePath: 'plans',
          sidebarPath: './sidebarsPlans.js',
          editUrl:
            'https://github.com/bricknpc/eloquent-tables/tree/HEAD/docs/',
        }),
      ],
      [
        '@docusaurus/plugin-ideal-image',
        {
            quality: 70,
            max: 1030, // max resized image's size.
            min: 640, // min resized image's size. if original is lower, use that size.
            steps: 2, // the max number of images generated between min and max (inclusive)
            disableInDev: false,
        },
      ],
    ]
};

export default config;
