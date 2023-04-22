import { precacheAndRoute } from "workbox-precaching";
importScripts("https://js.pusher.com/beams/service-worker.js");

precacheAndRoute(self.__WB_MANIFEST || []);

self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', () => self.clients.claim());
