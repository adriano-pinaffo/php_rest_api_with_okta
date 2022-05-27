import requests
import os
import re
import base64

class Token():

    def __init__(self, *env):
        env = env[0] if len(env) > 0 else '.env'
        self.env = self.load_env(env)
        try:
            self.scope = self.env['SCOPE']
            self.url = f"{self.env['OKTAISSUER']}/v1/token"
            self.key = self.env['OKTACLIENTID'];
            self.secret = self.env['OKTASECRET']
        except:
            pass

    def load_env(self, env):
        cwd = f"{os.getcwd()}/{env}"
        if (not os.path.isfile(cwd)):
            cwd = f"{os.getcwd()}/../{env}"
            if (not os.path.isfile(cwd)):
                return None
        file = None
        with open(cwd, 'r') as f:
            file = [line[:-1] for line in f.readlines()]
        file = {re.match('[^=]+', line)[0]: re.search('[^=]+$', line)[0] for line in file}
        return file

    def get_token(self):
        if not self.env:
            return None;
        payload = f"grant_type=client_credentials&scope={self.scope}"
        token = f"{self.key}:{self.secret}"
        token = str(base64.b64encode(bytes(token, encoding='utf-8')), encoding='utf-8')
        headers = {
                "Content-Type": "application/x-www-form-urlencoded",
                "Authorization": f"Basic {token}"
        }

        resp = requests.post(self.url, data=payload, headers=headers, timeout=5)
        if (not(resp.status_code >= 200 and resp.status_code < 300)):
            print(f'Error: {resp.status_code} ({resp.reason})')
            sys.exit(1)
            # raise ConnectionError
        jsonpy = resp.json()
        return f"{jsonpy['token_type']} {jsonpy['access_token']}"
        # return f"{jsonpy['access_token']}"

# token = Token()
# token.get_token()
