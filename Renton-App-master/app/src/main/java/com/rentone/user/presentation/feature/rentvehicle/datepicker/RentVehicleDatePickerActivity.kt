package com.rentone.user.presentation.feature.rentvehicle.datepicker
import android.app.Activity
import android.app.TimePickerDialog
import android.content.Intent
import android.os.Bundle
import android.view.MenuItem
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.core.view.isVisible
import com.rentone.user.R
import com.rentone.user.core.util.ViewUtils
import com.rentone.user.core.util.getSerializableExtraCompat
import com.rentone.user.databinding.ActivityRentVehicleDatePickerBinding
import com.rentone.user.domain.model.DateRange
import com.savvi.rangedatepicker.CalendarPickerView
import dagger.hilt.android.AndroidEntryPoint
import java.text.SimpleDateFormat
import java.util.Calendar
import java.util.Date
import java.util.Locale
import com.rentone.user.core.util.applyExitTransition

@AndroidEntryPoint
class RentVehicleDatePickerActivity : AppCompatActivity() {

    private lateinit var binding: ActivityRentVehicleDatePickerBinding

    private val param: HashMap<String, String> by lazy {
        intent.getSerializableExtraCompat<HashMap<String, String>>("param") ?: HashMap()
    }

    private var disableTime = false
    private val listDateBooked = mutableListOf<Date>()

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityRentVehicleDatePickerBinding.inflate(layoutInflater)
        setContentView(binding.root)

        instance = this

        supportActionBar?.apply {
            setDisplayHomeAsUpEnabled(true)
            elevation = 0f
        }

        disableTime = intent.getBooleanExtra("disableTime", false)

        val minDate = Calendar.getInstance().apply { add(Calendar.DATE, intent.getIntExtra("dayAdd", 0)) }
        val maxDate = Calendar.getInstance().apply { add(Calendar.MONTH, 6) }

        val vehicleBooked = intent.getSerializableExtraCompat<ArrayList<DateRange>>("dates_booked")
        vehicleBooked?.forEach { range ->
            listDateBooked.addAll(ViewUtils.getDates(range.startDate.orEmpty(), range.endDate.orEmpty()))
        }

        binding.calendarView.init(minDate.time, maxDate.time, SimpleDateFormat("dd MMM yyyy", Locale.getDefault()))
            .inMode(CalendarPickerView.SelectionMode.RANGE)
            .withHighlightedDates(listDateBooked)
        binding.calendarView.scrollToDate(Date())

        binding.calendarView.setOnDateSelectedListener(object : CalendarPickerView.OnDateSelectedListener {
            override fun onDateSelected(date: Date) {
                updateSelectedRange()
            }

            override fun onDateUnselected(date: Date) = Unit
        })

        if (disableTime) {
            binding.inputTimeLayout.isVisible = false
        } else {
            binding.inputTimeLayout.isVisible = true
            binding.inputTime.setOnClickListener { showTimePicker() }
        }

        binding.btnApply.setOnClickListener { onApplyClicked() }
    }

    private fun updateSelectedRange() {
        val selectedDates = binding.calendarView.selectedDates
        if (selectedDates.isEmpty()) return

        val dayFormat = SimpleDateFormat("dd MMM yyyy", Locale.getDefault())
        val paramFormat = SimpleDateFormat("yyyy-MM-dd", Locale.getDefault())
        val fromDateView = dayFormat.format(selectedDates[0])

        if (selectedDates.size == 1) {
            binding.txtRangeDate.text = fromDateView
            param["start_date"] = paramFormat.format(selectedDates[0])

            val dateEnd = Calendar.getInstance().apply {
                time = selectedDates[0]
                if (!disableTime) add(Calendar.DAY_OF_YEAR, 1)
            }
            param["end_date"] = paramFormat.format(dateEnd.time)
        } else {
            val toDateView = dayFormat.format(selectedDates[selectedDates.size - 1])
            binding.txtRangeDate.text = "$fromDateView - $toDateView"
            param["start_date"] = paramFormat.format(selectedDates[0])

            val dateEnd = Calendar.getInstance().apply {
                time = selectedDates[selectedDates.size - 1]
                if (!disableTime) add(Calendar.DAY_OF_YEAR, 1)
            }
            param["end_date"] = paramFormat.format(dateEnd.time)
        }
    }

    private fun showTimePicker() {
        val now = Calendar.getInstance()
        TimePickerDialog(
            this,
            { _, hourOfDay, minute ->
                val time = "${ViewUtils.convertDate(hourOfDay)}:${ViewUtils.convertDate(minute)}"
                param["time"] = time
                binding.inputTime.setText(time)
            },
            now.get(Calendar.HOUR_OF_DAY),
            now.get(Calendar.MINUTE),
            true
        ).show()
    }

    private fun onApplyClicked() {
        when {
            param["start_date"] == null -> {
                Toast.makeText(this, getString(R.string.date_not_selected), Toast.LENGTH_LONG).show()
            }
            param["time"] == null && !disableTime -> {
                Toast.makeText(this, getString(R.string.time_not_selected), Toast.LENGTH_LONG).show()
            }
            isDatesBooked() -> {
                Toast.makeText(this, getString(R.string.date_container_booked), Toast.LENGTH_LONG).show()
            }
            else -> {
                val resultIntent = Intent().putExtra("param", param)
                setResult(Activity.RESULT_OK, resultIntent)
                finish()
            }
        }
    }

    private fun isDatesBooked(): Boolean {
        val dates = ViewUtils.getDates(param["start_date"].orEmpty(), param["end_date"].orEmpty())
        return dates.any { date -> listDateBooked.any { it.time == date.time } }
    }

    override fun finish() {
        super.finish()
        applyExitTransition(R.anim.slide_in_left, R.anim.slide_out_right)
    }

    override fun onOptionsItemSelected(item: MenuItem): Boolean {
        if (item.itemId == android.R.id.home) {
            onBackPressedDispatcher.onBackPressed()
        }
        return true
    }

    companion object {
        var instance: RentVehicleDatePickerActivity? = null
    }
}
