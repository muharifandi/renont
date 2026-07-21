package com.rentone.user.presentation.feature.filter

import android.app.Activity
import android.content.Intent
import android.os.Bundle
import android.view.Menu
import android.view.MenuItem
import androidx.activity.viewModels
import androidx.appcompat.app.AppCompatActivity
import androidx.core.view.isVisible
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import com.google.android.material.chip.Chip
import com.rentone.user.R
import com.rentone.user.core.common.UiState
import com.rentone.user.databinding.ActivityListFilterBinding
import com.rentone.user.domain.model.FilterList
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch
import com.rentone.user.core.util.applyExitTransition

@AndroidEntryPoint
class ListFilterActivity : AppCompatActivity() {

    private lateinit var binding: ActivityListFilterBinding
    private val viewModel: ListFilterViewModel by viewModels()

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityListFilterBinding.inflate(layoutInflater)
        setContentView(binding.root)

        setupToolbar()
        setupListeners()
        observeState()
        
        // Initial visibility based on Intent
        binding.statusContainer.isVisible = intent.getBooleanExtra("status", false)
        binding.maxPassengerContainer.isVisible = intent.getBooleanExtra("maxPassenger", false)
        binding.priceRangeContainer.isVisible = intent.getBooleanExtra("priceRange", false)
        binding.vehicleFunctionalTypeContainer.isVisible = intent.getBooleanExtra("vehicleFunctionalType", false)
    }

    private fun setupToolbar() {
        setSupportActionBar(binding.toolbar)
        supportActionBar?.apply {
            setDisplayHomeAsUpEnabled(true)
            setHomeAsUpIndicator(R.drawable.ic_close)
        }
    }

    private fun setupListeners() {
        binding.btnApply.setOnClickListener {
            val resultIntent = Intent().apply {
                putExtra("filter", viewModel.getFinalFilter())
            }
            setResult(Activity.RESULT_OK, resultIntent)
            finish()
        }

        binding.groupStatus.setOnCheckedStateChangeListener { _, checkedIds ->
            val status = when (checkedIds.firstOrNull()) {
                R.id.chipActive -> 1
                R.id.chipInactive -> 0
                else -> -1
            }
            viewModel.updateStatus(status)
        }

        binding.groupMaxPassenger.setOnCheckedStateChangeListener { _, checkedIds ->
            val (min, max) = when (checkedIds.firstOrNull()) {
                R.id.chipTwoPeople -> 1 to 2
                R.id.chipFourPeople -> 3 to 4
                R.id.chipSixPeople -> 5 to 6
                R.id.chipMoreThanSixPeople -> 7 to 1000000
                else -> -1 to -1
            }
            viewModel.updatePassengerRange(min, max)
        }

        binding.rangePrice.addOnChangeListener { slider, _, _ ->
            val values = slider.values
            viewModel.updatePriceRange(values[0].toDouble(), values[1].toDouble())
            binding.inputMinPrice.setText(values[0].toInt().toString())
            binding.inputMaxPrice.setText(values[1].toInt().toString())
        }
    }

    private fun observeState() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                viewModel.filterState.collect { state ->
                    when (state) {
                        is UiState.Success -> updateUi(state.data)
                        is UiState.Error -> { /* Handle Error */ }
                        is UiState.Loading -> { /* Handle Loading */ }
                        else -> Unit
                    }
                }
            }
        }
    }

    private fun updateUi(filter: FilterList) {
        // Update Status
        val statusChipId = when (filter.status) {
            1 -> R.id.chipActive
            0 -> R.id.chipInactive
            else -> R.id.chipAll
        }
        binding.groupStatus.check(statusChipId)

        // Update Passenger
        val passengerChipId = when (filter.minPassenger) {
            1 -> R.id.chipTwoPeople
            3 -> R.id.chipFourPeople
            5 -> R.id.chipSixPeople
            7 -> R.id.chipMoreThanSixPeople
            else -> R.id.chipNone
        }
        binding.groupMaxPassenger.check(passengerChipId)

        // Update Price
        binding.rangePrice.valueFrom = filter.priceMin.toFloat()
        binding.rangePrice.valueTo = filter.priceMax.toFloat()
        binding.rangePrice.values = listOf(
            filter.minPrice.coerceIn(filter.priceMin, filter.priceMax).toFloat(),
            filter.maxPrice.coerceIn(filter.priceMin, filter.priceMax).toFloat()
        )

        // Update Functional Type Chips
        setupFunctionalTypeChips(filter)
    }

    private fun setupFunctionalTypeChips(filter: FilterList) {
        binding.groupVehicleFunctionalType.removeAllViews()
        filter.vehicleFunctionalType.forEach { type ->
            val chip = Chip(this).apply {
                text = type.name
                isCheckable = true
                isChecked = filter.vehicleFunctionalTypeIdSelected.contains(type.id.toString())
                setOnCheckedChangeListener { _, _ ->
                    viewModel.toggleFunctionalType(type.id.toString())
                }
            }
            binding.groupVehicleFunctionalType.addView(chip)
        }
    }

    override fun onCreateOptionsMenu(menu: Menu): Boolean {
        menuInflater.inflate(R.menu.filter, menu)
        return true
    }

    override fun onOptionsItemSelected(item: MenuItem): Boolean {
        return when (item.itemId) {
            android.R.id.home -> {
                onBackPressedDispatcher.onBackPressed()
                true
            }
            R.id.action_reset -> {
                viewModel.resetFilters()
                true
            }
            else -> super.onOptionsItemSelected(item)
        }
    }

    override fun finish() {
        super.finish()
        applyExitTransition(R.anim.slide_in_left, R.anim.slide_out_right)
    }
}
