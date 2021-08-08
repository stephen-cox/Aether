/**
 * Logging library.
 */

class Logger {

  static DEBUG = { level: 1, status: 'Debug' };
  static INFO = { level: 2, status: 'Info' };
  static NOTICE = { level: 3, status: 'Notice' };
  static WARN = { level: 4, status: 'Warn' };
  static ERROR = { level: 5, status: 'Error' };

  constructor(debug = false) {
    this.debug = debug;
    this.logs = [];
  }

  log(message, level = Logger.NOTICE, module = 'default') {
    const log_item = {
      date: new Date(),
      level: level,
      message: message,
      module: module,
    };
    if (this.debug) {
      console.log(this.logItemToString(log_item));
    }
    this.logs.push(log_item);
  }

  debug(message, module = 'default') {
    this.log(message, Logger.DEBUG, module);
  }

  info(message, module = 'default') {
    this.log(message, Logger.INFO, module);
  }

  notice(message, module = 'default') {
    this.log(message, Logger.NOTICE, module);
  }

  warn(message, module = 'default') {
    this.log(message, Logger.WARN, module);
  }

  error(message, module = 'default') {
    this.log(message, Logger.ERROR, module);
  }

  logItemToString(log_item) {
    return `${log_item.date.toISOString()} ${log_item.level.status}: ${log_item.module} - ${log_item.message}`;
  }

}

export default Logger;
