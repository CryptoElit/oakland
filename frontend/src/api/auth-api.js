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


  async login({ email, password }) {



	  return new Promise((resolve, reject) => {
		  try {
			  // Find the user
			  fetch('http://localhost:7878/users/login', {
				  method: 'POST', // *GET, POST, PUT, DELETE, etc.
				  body: JSON.stringify({username: email, password})
			  }).then((e) => {
				  alert(JSON.stringify(e))
				  if (e) {
					  const accessToken = sign({userId: e}, JWT_SECRET, {expiresIn: JWT_EXPIRES_IN});

					  resolve(e)
				  } else {reject(e)}
			  })
		  } catch (err) {
			  console.error('[Auth Api]: ', err);
			  reject(new Error('Internal server error'));
		  }
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

        resolve(accessToken);
      } catch (err) {
        console.error('[Auth Api]: ', err);
        reject(new Error('Internal server error'));
      }
    });
  }

  me(accessToken) {

    return new Promise((resolve, reject) => {
      try {
        // Decode access token
        const { userId } = decode(accessToken);

        // Find the user
        const user = users.find((_user) => _user.id === userId);

        if (!user) {
          reject(new Error('Invalid authorization token'));
          return;
        }

        resolve({
          id: user.id,
          avatar: user.avatar,
          email: user.email,
          name: user.name,
          plan: user.plan
        });
      } catch (err) {

        console.error('[Auth Api]: ', err);
        reject(new Error('Internal server error'));
      }
    });
  }
}

export const authApi = new AuthApi();
