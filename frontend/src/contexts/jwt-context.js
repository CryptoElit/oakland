import {createContext, useEffect, useReducer} from 'react';
import PropTypes from 'prop-types';
import {useSelector} from "../store";
import {decode, JWT_EXPIRES_IN, JWT_SECRET, sign} from '../utils/jwt';

let ActionType;
(function (ActionType) {
	ActionType['INITIALIZE'] = 'INITIALIZE';
	ActionType['LOGIN'] = 'LOGIN';
	ActionType['LOGOUT'] = 'LOGOUT';
	ActionType['REGISTER'] = 'REGISTER';
})(ActionType || (ActionType = {}));

const initialState = {
	isAuthenticated: false,
	isInitialized: false,
	user: null
};

const handlers = {
	INITIALIZE: (state, action) => {
		const { isAuthenticated, user } = action.payload;

		return {
			...state,
			isAuthenticated,
			isInitialized: true,
			user
		};
	},
	LOGIN: (state, action) => {
		const { user } = action.payload;

		return {
			...state,
			isAuthenticated: true,
			user
		};
	},
	LOGOUT: (state) => ({
		...state,
		isAuthenticated: false,
		user: null
	}),
	REGISTER: (state, action) => {
		const { user } = action.payload;

		return {
			...state,
			isAuthenticated: true,
			user
		};
	}
};

const reducer = (state, action) => (handlers[action.type]
	? handlers[action.type](state, action)
	: state);

export const AuthContext = createContext({
	...initialState,
	platform: 'JWT',
	login: () => Promise.resolve(),
	logout: () => Promise.resolve(),
	register: () => Promise.resolve()
});

export const AuthProvider = (props) => {
	const { children } = props;
	const [state, dispatch] = useReducer(reducer, initialState);
	const { user } = useSelector((state) => state.user);

	useEffect(() => {
		const initialize = async () => {
			try {
				const accessToken = globalThis.localStorage.getItem('rAcc');

				if (accessToken) {
					await me();

					dispatch({
						type: ActionType.INITIALIZE,
						payload: {
							isAuthenticated: true,
							user
						}
					});
				} else {
					dispatch({
						type: ActionType.INITIALIZE,
						payload: {
							isAuthenticated: false,
							user: null
						}
					});
				}
			} catch (err) {
				console.error(err);
				dispatch({
					type: ActionType.INITIALIZE,
					payload: {
						isAuthenticated: false,
						user: null
					}
				});
			}
		};

		initialize();
	}, []);

	const postData = async (url = '', data = {}, token) => {
		// Default options are marked with *
		let dStream = {
			method: 'POST', // *GET, POST, PUT, DELETE, etc.
			mode: 'cors', // no-cors, *cors, same-origin
			cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
			body: JSON.stringify(data), // body data type must match "Content-Type" header
		};
		if (token) {
			dStream = {...dStream, token}
		}
		const response = await fetch(url, dStream);

		return response.json(); // parses JSON response into native JavaScript objects
	}


	const login = async (email, password) => {

		const response = await postData('http://localhost:7878/users/login', {username: email, password});

		const wrappedRes = sign(response, JWT_SECRET, { expiresIn: JWT_EXPIRES_IN });

		localStorage.setItem('pAcc', wrappedRes);

		const accessToken = sign(email, JWT_SECRET, { expiresIn: JWT_EXPIRES_IN });
		localStorage.setItem('rAcc', accessToken);

		const user = await me();
	if (user) {
		dispatch({
			type: ActionType.LOGIN,
			payload: user
		});
	}
	};

	const me = async () => {


			try {
				let wrappedP = localStorage.getItem('pAcc')
				let wrappedR = localStorage.getItem('rAcc');
				if (wrappedP && wrappedR) {
					const unwrappedP = decode(wrappedP);
					const unwrappedR = decode(wrappedR);

					const response = await postData('http://localhost:7878/users/check', {
						username: unwrappedR,
						token: unwrappedP
					});


					return response;
				} else {
					return false;
				}
			} catch (err) {

				console.error('[Auth Api]: ', err);
				return false;
			}

	}

	const logout = async () => {
		localStorage.removeItem('accessToken');
		dispatch({ type: ActionType.LOGOUT });
	};


	return (
		<AuthContext.Provider
			value={{
				...state,
				platform: 'JWT',
				login,
				logout,
			}}
		>
			{children}
		</AuthContext.Provider>
	);
};

AuthProvider.propTypes = {
	children: PropTypes.node.isRequired
};

export const AuthConsumer = AuthContext.Consumer;
