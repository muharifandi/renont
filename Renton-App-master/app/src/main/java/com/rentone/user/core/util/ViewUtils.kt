package com.rentone.user.core.util

import android.text.TextWatcher
import android.widget.EditText
import org.json.JSONObject
import java.text.DecimalFormat
import java.text.NumberFormat
import java.text.SimpleDateFormat
import java.util.*

object ViewUtils {

    fun numberToDecimalText(watcher: TextWatcher, editText: EditText, textValue: CharSequence) {
        editText.removeTextChangedListener(watcher)
        try {
            val originalString = textValue.toString().replace(",", "")
            val longVal = originalString.toLong()
            val formatter = NumberFormat.getInstance(Locale.US) as DecimalFormat
            formatter.applyPattern("#,###,###,###")
            val formattedString = formatter.format(longVal)
            editText.setText(formattedString)
            editText.setSelection(editText.text.length)
        } catch (e: Exception) {
            e.printStackTrace()
        }
        editText.addTextChangedListener(watcher)
    }

    fun numberToDecimalText(value: Double): String {
        return try {
            val formatter = NumberFormat.getInstance(Locale.US) as DecimalFormat
            formatter.applyPattern("#,###,###,###")
            formatter.format(value)
        } catch (e: Exception) {
            "0"
        }
    }

    fun formatCurrency(value: Double): String {
        return NumberFormat.getNumberInstance(Locale.GERMANY).format(value)
    }

    fun convertDate(input: Int): String {
        return if (input >= 10) input.toString() else "0$input"
    }

    fun getCountOfDays(createdDateString: String, expireDateString: String): String {
        val dateFormat = SimpleDateFormat("yyyy-MM-dd", Locale.getDefault())
        return try {
            val date1 = dateFormat.parse(createdDateString)
            val date2 = dateFormat.parse(expireDateString)
            val diff = date2!!.time - date1!!.time
            (diff / (24 * 60 * 60 * 1000)).toInt().toString()
        } catch (e: Exception) {
            "0"
        }
    }

    fun mysqlDateToNormalDate(mysqlDate: String, mysqlFormat: String, targetFormat: String): String {
        return try {
            val spf = SimpleDateFormat(mysqlFormat, Locale.getDefault())
            val date = spf.parse(mysqlDate)
            val targetSpf = SimpleDateFormat(targetFormat, Locale.getDefault())
            targetSpf.format(date!!)
        } catch (e: Exception) {
            ""
        }
    }

    fun getDates(dateString1: String, dateString2: String): List<Date> {
        val dates = mutableListOf<Date>()
        val df = SimpleDateFormat("yyyy-MM-dd", Locale.getDefault())
        return try {
            val date1 = df.parse(dateString1)
            val date2 = df.parse(dateString2)
            val cal = Calendar.getInstance()
            cal.time = date1!!
            while (!cal.time.after(date2)) {
                dates.add(cal.time)
                cal.add(Calendar.DATE, 1)
            }
            dates
        } catch (e: Exception) {
            emptyList()
        }
    }

    fun jsonValue(obj: JSONObject, key: String, defValue: String): String {
        return try { obj.getString(key) } catch (e: Exception) { defValue }
    }

    fun jsonValue(obj: JSONObject, key: String, defValue: Int): Int {
        return try { obj.getInt(key) } catch (e: Exception) { defValue }
    }

    fun jsonValue(obj: JSONObject, key: String, defValue: Double): Double {
        return try { obj.getDouble(key) } catch (e: Exception) { defValue }
    }
}
