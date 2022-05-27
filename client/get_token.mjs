import fetch from 'node-fetch';
import path from 'path';
import { fileURLToPath } from 'url';
import { readFileSync } from 'node:fs';

export class Token {
  constructor(env = '.env') {
    this.urisuffix = '/v1/token';
    this.loadEnv(env);
  }

  loadEnv(env) {
    const __filename = fileURLToPath(import.meta.url);
    const __dirname = path.dirname(__filename);
    let envPath = __dirname + '/' + env;
    this.readFileRecursive(envPath);
  }

  readFileRecursive(envPath) {
    let env = envPath.match(/[^\/]+$/)[0];
    try {
      let obj = {};
      let data = readFileSync(envPath, 'utf8');
      data
        .split('\n')
        .filter(line => line != '')
        .forEach(line => (obj[line.replace(/=.*$/, '')] = line.replace(/^.*=/, '')));

      for (let [key, value] of Object.entries(obj)) this[key] = value === '0' ? false : value === '1' ? true : value;
    } catch (err) {
      envPath = envPath.replace(/[^\/]+\/([^\/]+)$/, '$1');
      if (envPath != `/${env}`) this.readFileRecursive(envPath);
      else throw new Error(`${env} file not found`);
    }
  }

  async getTokenAsync() {
    let payload = `grant_type=client_credentials&scope=${this.SCOPE}`;
    let token = Buffer.from(this.OKTACLIENTID + ':' + this.OKTASECRET).toString('base64');
    let url = this.OKTAISSUER + this.urisuffix;
    try {
      const options = {
        method: 'post',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          Authorization: `Basic ${token}`,
        },
        body: payload,
      };
      let result = await fetch(url, options);
      let json = await result.json();
      return Promise.resolve(json.token_type + ' ' + json.access_token);
    } catch (err) {
      throw new Error('Error: ' + err);
    }
  }
}

//const token = new Token();
//token.getTokenAsync().then(result => console.log(result));
