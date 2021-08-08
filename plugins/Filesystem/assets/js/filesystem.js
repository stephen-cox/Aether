/**
 * JavaScript for interacting with the Filesystem API.
 */

import axios from "axios";
import qs from 'qs'
import Logger from '../../../../assets/js/logger';

// API version.
const VERSION = 1;

/**
 * Filesystem class.
 */
class Filesystem {

  /**
   * Initialise new Filesystem class.
   */
  constructor(logger = false) {

    // Set logger.
    if (logger) {
      this.log = logger;
    }
    else {
      this.log = new Logger(true);
    }
  }

  /**
   * Make a request to the API.
   */
  async request(method, url, data = {}) {
    try {
      const response = await axios.request({
        method: method,
        url: url,
        data: data,
        headers: {
          'Content-Type': 'application/json',
        },
      });
      console.log(response);
      this.log.notice(`${response.data.status} - ${method} request to ${url}`, 'filesystem');
      return response.data;
    }
    catch (error) {
      if (error.response.data.status == 'fail') {
        this.log.error(`${error.response.data.status} - ${error.response.data.data.message} - ${method} request to ${url}`, 'filesystem');
        return error.response.data;
      }
      else {
        this.log.error(`${error.response.data.status} - ${error.response.data.message} - ${method} request to ${url}`, 'filesystem');
        return {};
      }
    }
  }

  /**
   * Open file.
   */
  async openFile(filename) {
    const encoded = btoa(filename);
    return this.request('get', `/api/1/fs/${encoded}/file`)
    .then(response => response);
  }

  /**
   * Save file.
   */
  async saveFile(filename, contents) {
    const encoded = btoa(filename);
    return this.request('patch', `/api/1/fs/${encoded}/file`, { content: contents })
    .then(response => response);
  }

}

export default Filesystem;
