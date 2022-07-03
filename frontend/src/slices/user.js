import { createSlice } from '@reduxjs/toolkit';
import { calendarApi } from '../api/calendar-api';
import {objFromArray} from "../utils/obj-from-array";


const postData = async (url = '', data = {}) => {
	// Default options are marked with *
	const response = await fetch('http://localhost:7878/users/login', {
		method: 'POST', // *GET, POST, PUT, DELETE, etc.
		mode: 'cors', // no-cors, *cors, same-origin
		cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
		body: JSON.stringify(data) // body data type must match "Content-Type" header
	});
	return response.json(); // parses JSON response into native JavaScript objects
}


const initialState = {
    user: []
};

const slice = createSlice({
  name: 'user',
  initialState,
  reducers: {
    getUser(state, action) {

		state.user = action.payload;

	},
    createUser(state, action) {


      state.user = action.payload;


    },
    updateUser(state, action) {
      const user = action.payload;

      state.user = state.user.map((_user) => {
        if (_user.id === user.id) {
          return user;
        }

        return _user;
      });
    },
    deleteUser(state, action) {
      state.user = state.user.filter((user) => user.id !== action.payload);
    }
  }
});

export const { reducer } = slice;

export const getUser = () => async (dispatch) => {

  dispatch(slice.actions.getUser());
};

export const createUser = (data) => async (dispatch) => {

  dispatch(slice.actions.createUser(data));
};

export const updateUser = (userId, update) => async (dispatch) => {

  dispatch(slice.actions.updateUser(data));
};

export const deleteUser = (userId) => async (dispatch) => {

  dispatch(slice.actions.deleteUser(userId));
};
