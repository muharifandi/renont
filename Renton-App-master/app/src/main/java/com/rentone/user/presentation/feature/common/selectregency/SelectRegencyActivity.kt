package com.rentone.user.presentation.feature.common.selectregency

import android.app.Activity
import android.app.SearchManager
import android.content.Context
import android.content.Intent
import android.os.Bundle
import android.view.Menu
import android.view.MenuItem
import android.widget.ArrayAdapter
import android.widget.LinearLayout
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import androidx.appcompat.widget.SearchView
import androidx.activity.viewModels
import androidx.core.view.isVisible
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import com.rentone.user.R
import com.rentone.user.core.common.UiState
import com.rentone.user.databinding.ActivitySelectRegencyBinding
import com.rentone.user.domain.model.Regencies
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch
import com.rentone.user.core.util.applyExitTransition

@AndroidEntryPoint
class SelectRegencyActivity : AppCompatActivity() {

    private lateinit var binding: ActivitySelectRegencyBinding
    private val viewModel: SelectRegencyViewModel by viewModels()

    private var searchQuery: String = ""

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivitySelectRegencyBinding.inflate(layoutInflater)
        setContentView(binding.root)

        setSupportActionBar(binding.toolbar)
        supportActionBar?.apply {
            setDisplayHomeAsUpEnabled(true)
            setDisplayShowHomeEnabled(true)
        }

        searchQuery = intent.getStringExtra("regency_name").orEmpty()
        observeState()
        viewModel.search(searchQuery)
    }

    private fun observeState() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                viewModel.regencies.collect { state -> handleUiState(state) }
            }
        }
    }

    private fun handleUiState(state: UiState<List<Regencies>>) {
        when (state) {
            is UiState.Loading -> {
                binding.txtListMessage.isVisible = true
                binding.txtListMessage.text = getString(R.string.please_wait)
                binding.list.isVisible = false
            }
            is UiState.Success -> {
                if (state.data.isNotEmpty()) {
                    bindList(state.data)
                    binding.txtListMessage.isVisible = false
                    binding.list.isVisible = true
                } else {
                    binding.txtListMessage.isVisible = true
                    binding.txtListMessage.text = getString(R.string.no_data_available)
                    binding.list.isVisible = false
                }
            }
            is UiState.Error -> {
                AlertDialog.Builder(this)
                    .setTitle(title)
                    .setMessage(getString(R.string.failed_check_to_server))
                    .setPositiveButton(R.string.yes) { _, _ -> finish() }
                    .show()
            }
            else -> Unit
        }
    }

    private fun bindList(data: List<Regencies>) {
        val adapter = ArrayAdapter(this, android.R.layout.simple_list_item_1, data.map { it.name }.toTypedArray())
        binding.list.apply {
            choiceMode = android.widget.ListView.CHOICE_MODE_SINGLE
            setAdapter(adapter)
            setOnItemClickListener { _, _, position, _ ->
                val regenciesId = data[position].id
                val resultIntent = Intent().putExtra("regencies_id", regenciesId)
                setResult(Activity.RESULT_OK, resultIntent)
                finish()
            }
        }
    }

    override fun onCreateOptionsMenu(menu: Menu): Boolean {
        menuInflater.inflate(R.menu.select_regency, menu)

        val searchManager = getSystemService(Context.SEARCH_SERVICE) as SearchManager
        val searchView = menu.findItem(R.id.app_bar_search).actionView as SearchView
        searchView.setSearchableInfo(searchManager.getSearchableInfo(componentName))
        searchView.setIconifiedByDefault(true)

        val searchEditFrame = searchView.findViewById<LinearLayout>(androidx.appcompat.R.id.search_edit_frame)
        (searchEditFrame.layoutParams as LinearLayout.LayoutParams).leftMargin = 0
        searchView.maxWidth = Integer.MAX_VALUE
        searchView.setQuery(searchQuery, false)
        searchView.setOnQueryTextListener(object : SearchView.OnQueryTextListener {
            override fun onQueryTextSubmit(query: String?): Boolean = true

            override fun onQueryTextChange(newText: String?): Boolean {
                searchQuery = newText.orEmpty()
                viewModel.search(searchQuery)
                return true
            }
        })

        return super.onCreateOptionsMenu(menu)
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
}
