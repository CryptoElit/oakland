import { createSlice } from '@reduxjs/toolkit';
import { calendarApi } from '../api/calendar-api';
import {objFromArray} from "../utils/obj-from-array";

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
      state.user.push(action.payload);
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
