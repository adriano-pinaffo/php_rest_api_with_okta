`
HOW TO RUN:

Have the server running first:
$ php -S 127.0.0.1:8000 -t public

List all users:
$ node client.mjs getallusers
# adjust the prints for the output

List a user ID:
$ node client.mjs getuser 2
# adjust the prints for the output

Add a user:
$ node client.mjs adduser '{"firstname": "Dennis", "lastname": "Ritchie", "firstparent_id": 4, "secondparent_id": 3}'

Update a user by ID:
$ node client.mjs updateuser 90 '{"firstname": "Dennys", "lastname": "Ritchie", "firstparent_id": 4, "secondparent_id": 3}

Delete a user by ID:
$ node client.mjs deleteuser 89
`;

import fetch from 'node-fetch';
import { argv } from 'process';
import { exit } from 'process';
import { Token } from './get_token.mjs';

// getallusers|getuser|adduser|updateuser|deleteuser
const tokenObj = new Token();

let url = 'http://127.0.0.1:8000/person';
const command = argv[2];
let id = null;
let input = null;
switch (command) {
  case 'getallusers':
    fetchget();
    break;
  case 'getuser':
    id = typeof argv[3] == 'undefined' ? '' : '/' + argv[3];
    fetchget(id);
    break;
  case 'adduser':
    input = argv[3];
    fetchpost(input);
    break;
  case 'updateuser':
    id = typeof argv[3] == 'undefined' ? '' : '/' + argv[3];
    input = argv[4];
    fetchupdate(id, input);
    break;
  case 'deleteuser':
    id = typeof argv[3] == 'undefined' ? '' : '/' + argv[3];
    fetchdelete(id, input);
    break;
  default:
    console.log('Command not found!');
    console.log('Command = getallusers|getuser|adduser|updateuser|deleteuser');
    exit(1);
}

async function fetchget(id) {
  url += id ? id : '';
  const data = await fetchapi(url, 'get', input);
  //printJson(data); // print in json format
  //printMatrix(data); // print 2-dimensional array
  printCsv(data);
}

async function fetchpost(input) {
  const data = await fetchapi(url, 'post', input);
  console.log('ID:', data['id']);
}

async function fetchupdate(id, input) {
  url += id;
  const data = await fetchapi(url, 'put', input);
  console.log('Rows:', data['rows']);
}

async function fetchdelete(id) {
  url += id;
  const data = await fetchapi(url, 'delete');
  console.log('Rows:', data['rows']);
}

async function fetchapi(url, method, input) {
  const token = await tokenObj.getTokenAsync();
  const options = loadOptions(method, token, input);
  const response = await fetch(url, options);
  if (!(response.status >= 200 && response.status < 300)) {
    console.log(`Error: ${response.status} (${response.statusText})`);
    exit(1);
  }
  const data = await response.json();
  if (method.toLowerCase() != 'get')
    console.log(`Status code: ${response.status} (${response.statusText})`);
  return data;
}

function printJson(data) {
  console.log(data);
}

function printMatrix(data) {
  const header = Object.keys(data[0]);
  let table = [[...header]];
  data.forEach(obj => {
    let row = [];
    header.forEach(item => {
      row.push(obj[item]);
    });
    table.push(row);
  });
  console.log(table);
}

function printCsv(data) {
  const header = Object.keys(data[0]);
  console.log(header.join(', '));

  data.forEach(obj => {
    let row = [];
    header.forEach(item => {
      row.push(obj[item]);
    });
    console.log(row.join(','));
  });
}

function loadOptions(method, token, input) {
  const headers = {
    'Content-Type': 'application/json',
    Authorization: token,
  };
  const options = {
    method: method,
    headers: headers,
    body: input,
  };
  return options;
}
