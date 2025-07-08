import React from "react";

const StatBox = ({ title, value, icon}) => {
  return (
    <div className="bg-white dark:bg-gray-800 rounded-xl p5 shadow flex items-center space-x-4 w-full">
      {icon && (
        <div className="text-4xl text-indigo-500">
          {icon}
        </div>
      )}
      <div>
        <div className="text-gray-500 uppercase font-semibold text-sm">{title}</div>
        <div className="text-3xl font-bold">{value}</div>
      </div>
    </div>
  );
}

export default StatBox;