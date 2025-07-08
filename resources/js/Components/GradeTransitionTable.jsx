import React from "react";

export default function GradeTransitionTable({ rows }) {
  return(
    <div className="overflow-auto">
      <table className="table-auto border-collapse w-full text-sm">
        <thead>
          <tr className="bg-gray-100">
            <th className="border p-2">From</th>
            <th className="border p-2">To</th>
            <th className="border p-2 text-right">Count</th>
          </tr>
        </thead>
        <tbody>
          {rows.map((row, i) => (
            <tr key={i} className="hover:bg-gray-50">
              <td className="border p-2">{row.from}</td>
              <td className="border p-2">{row.to}</td>
              <td className="border p-2 text-right">{row.count.toLocaleString()}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  )
}