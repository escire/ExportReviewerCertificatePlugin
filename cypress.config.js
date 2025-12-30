import { defineConfig } from 'cypress'
import dotenv from 'dotenv'
import fs from 'fs'

// Load environment variables from .env file
dotenv.config()

export default defineConfig({
    e2e: {
        baseUrl: process.env.CYPRESS_BASE_URL || 'https://ojsdesa33.escire.site',

        // Timeouts
        defaultCommandTimeout: 10000,
        requestTimeout: 10000,
        responseTimeout: 30000,
        pageLoadTimeout: 60000,

        // Viewport
        viewportWidth: 1280,
        viewportHeight: 720,

        // Downloads folder
        downloadsFolder: 'cypress/downloads',

        // Retry configuration
        retries: {
            runMode: 2,
            openMode: 0
        },

        // Screenshots and videos
        screenshotOnRunFailure: true,
        video: true,
        videoCompression: 32,

        // Browser
        chromeWebSecurity: false,

        setupNodeEvents(on, config) {
            // File exists task
            on('task', {
                fileExists(filename) {
                    return fs.existsSync(filename)
                }
            })

            // Pass environment variables to Cypress
            config.env.adminUser = process.env.CYPRESS_ADMIN_USER || config.env.adminUser || 'admin'
            config.env.adminPassword = process.env.CYPRESS_ADMIN_PASSWORD || config.env.adminPassword || 'admin'
            config.env.reviewerUser = process.env.CYPRESS_REVIEWER_USER || config.env.reviewerUser || 'reviewer'
            config.env.reviewerPassword = process.env.CYPRESS_REVIEWER_PASSWORD || config.env.reviewerPassword || 'reviewer'
            config.env.testSubmissionId = process.env.CYPRESS_TEST_SUBMISSION_ID || config.env.testSubmissionId || '1'
            config.env.journalPath = process.env.CYPRESS_JOURNAL_PATH || config.env.journalPath || 'rdp'

            // Log loaded values for debugging
            console.log('Cypress Environment Variables:')
            console.log('- adminUser:', config.env.adminUser)
            console.log('- reviewerUser:', config.env.reviewerUser)
            console.log('- testSubmissionId:', config.env.testSubmissionId)
            console.log('- journalPath:', config.env.journalPath)

            return config
        },

        // Spec pattern
        specPattern: 'cypress/e2e/**/*.cy.{js,jsx,ts,tsx}',

        // Support file
        supportFile: 'cypress/support/e2e.js',

        // Environment variables
        env: {
            adminUser: process.env.CYPRESS_ADMIN_USER || 'admin',
            adminPassword: process.env.CYPRESS_ADMIN_PASSWORD || 'admin',
            reviewerUser: process.env.CYPRESS_REVIEWER_USER || 'reviewer',
            reviewerPassword: process.env.CYPRESS_REVIEWER_PASSWORD || 'reviewer'
        }
    }
})
