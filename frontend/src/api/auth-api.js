import { createResourceId } from '../utils/create-resource-id';
import { decode, JWT_EXPIRES_IN, JWT_SECRET, sign } from '../utils/jwt';

import { wait } from '../utils/wait';

const users = [
  {
    id: '5e86809283e28b96d2d38537',
    avatar: '/static/mock-images/avatars/avatar-anika_visser.png',
    email: 'josephhart127001@gmail.com',
    name: 'Anika Visser',
    password: 'go',
    plan: 'Premium'
  }
];

class AuthApi {
// Example POST method implementation:
	  postData = async (url = '', data = {}) => {
		// Default options are marked with *
		const response = await fetch('http://localhost:7878/users/login', {
			method: 'POST', // *GET, POST, PUT, DELETE, etc.
			mode: 'cors', // no-cors, *cors, same-origin
			cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
			body: JSON.stringify(data) // body data type must match "Content-Type" header
		});
		return response.json(); // parses JSON response into native JavaScript objects
	}


  async login({ email, password }) {
	  const accessToken = sign(email, JWT_SECRET, {expiresIn: JWT_EXPIRES_IN});
	  return await this.postData('http://localhost:7878/users/login', {username: email, password, token: accessToken})
		  .then((d) => {
			  return {user: JSON.parse(d), token: accessToken}
		  })
  }

  async register({ email, name, password }) {
    await wait(1000);

    return new Promise((resolve, reject) => {
      try {
        // Check if a user already exists
        let user = users.find((_user) => _user.email === email);

        if (user) {
          reject(new Error('User already exists'));
          return;
        }

        user = {
          id: createResourceId(),
          avatar: undefined,
          email,
          name,
          password,
          plan: 'Standard'
        };

        users.push(user);

        const accessToken = sign({ userId: user.id }, JWT_SECRET, { expiresIn: JWT_EXPIRES_IN });

        resolve(user, accessToken);
      } catch (err) {
        console.error('[Auth Api]: ', err);
        reject(new Error('Internal server error'));
      }
    });
  }

  me(accessToken, user) {
	  const d = decode(accessToken);


	  alert( d)
	  alert( user.email)
	  return new Promise((resolve, reject) => {
		  try {
			  const d = decode(accessToken);


			  if (!user || d !== user.email) {
				  reject(new Error('Invalid authorization token'));
				  return;
			  }

			  resolve(user);
		  } catch (err) {

			  console.error('[Auth Api]: ', err);
			  reject(new Error('Internal server error'));
		  }
	  })
  }
}

export const authApi = new AuthApi();
